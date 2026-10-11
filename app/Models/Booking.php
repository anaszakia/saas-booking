<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use App\Traits\HasUlid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Booking extends Model
{
    use SoftDeletes, HasAuditColumns, BelongsToTenant, HasUlid;

    protected $fillable = [
        'ulid',
        'tenant_id',
        'venue_id',
        'resource_id',
        'customer_id',
        'user_id',
        'booking_code',
        'start_at',
        'end_at',
        'subtotal',
        'addon_total',
        'discount_total',
        'total_amount',
        'status',
        'payment_status',
        'hold_expires_at',
        'notes',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected $casts = [
        'start_at'        => 'datetime',
        'end_at'          => 'datetime',
        'hold_expires_at' => 'datetime',
        'cancelled_at'    => 'datetime',
        'subtotal'        => 'decimal:2',
        'addon_total'     => 'decimal:2',
        'discount_total'  => 'decimal:2',
        'total_amount'    => 'decimal:2',
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function addons()
    {
        return $this->hasMany(BookingAddon::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * ULTRA FAST AVAILABILITY CHECK (RAW QUERY DIRECT & ANTI DOUBLE BOOKING)
     * Menggunakan composite index idx_bookings_availability
     */
    public static function isSlotAvailable(int $tenantId, int $resourceId, string $startAt, string $endAt, ?int $ignoreBookingId = null): bool
    {
        $now = Carbon::now();

        $query = DB::table('bookings')
            ->where('tenant_id', $tenantId)
            ->where('resource_id', $resourceId)
            ->whereNull('deleted_at')
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->where(function ($holdCheck) use ($now) {
                // Booking aktif, ATAU booking pending yang hold_expires_at nya belum lewat
                $holdCheck->where('status', '!=', 'pending')
                          ->orWhere(function ($hold) use ($now) {
                              $hold->where('status', 'pending')
                                   ->where('hold_expires_at', '>', $now);
                          });
            })
            ->where(function ($timeOverlap) use ($startAt, $endAt) {
                $timeOverlap->where('start_at', '<', $endAt)
                            ->where('end_at', '>', $startAt);
            });

        if ($ignoreBookingId) {
            $query->where('id', '!=', $ignoreBookingId);
        }

        return !$query->exists();
    }

    /**
     * BUAT BOOKING BARU DENGAN PESSIMISTIC LOCKING (ANTI-RACE CONDITION)
     */
    public static function createBookingWithLock(array $data, array $addons = []): self
    {
        return DB::transaction(function () use ($data, $addons) {
            $tenantId   = $data['tenant_id'];
            $resourceId = $data['resource_id'];
            $startAt    = $data['start_at'];
            $endAt      = $data['end_at'];

            // 1. Pessimistic lock pada resource agar transaksi konkuren mengantri
            DB::table('resources')->where('id', $resourceId)->lockForUpdate()->first();

            // 2. Cek ketersediaan slot
            if (!static::isSlotAvailable($tenantId, $resourceId, $startAt, $endAt)) {
                throw new \Exception('Slot waktu yang dipilih sudah terisi atau sedang dalam proses pembayaran.');
            }

            // 3. Generate booking code unik
            $bookingCode = 'BK-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            // 4. Insert booking
            $booking = static::create(array_merge($data, [
                'booking_code'    => $bookingCode,
                'status'          => $data['status'] ?? 'pending',
                'hold_expires_at' => $data['hold_expires_at'] ?? Carbon::now()->addMinutes(15),
            ]));

            // 5. Insert addons jika ada
            if (!empty($addons)) {
                foreach ($addons as $addon) {
                    BookingAddon::create([
                        'booking_id' => $booking->id,
                        'addon_id'   => $addon['id'],
                        'addon_name' => $addon['name'],
                        'price'      => $addon['price'],
                        'quantity'   => $addon['quantity'],
                        'subtotal'   => $addon['price'] * $addon['quantity'],
                    ]);
                }
            }

            return $booking;
        });
    }

    /**
     * Release booking pending yang hold nya sudah expired (Scheduler Job)
     */
    public static function releaseExpiredHolds(): int
    {
        return DB::transaction(function () {
            $now = Carbon::now();

            // 1. Ambil ID booking yang sudah expired
            $expiredBookingIds = DB::table('bookings')
                ->where('status', 'pending')
                ->where('hold_expires_at', '<=', $now)
                ->pluck('id')
                ->toArray();

            if (empty($expiredBookingIds)) {
                return 0;
            }

            // 2. Update status booking menjadi cancelled
            $count = DB::table('bookings')
                ->whereIn('id', $expiredBookingIds)
                ->update([
                    'status'              => 'cancelled',
                    'cancellation_reason' => 'Auto cancelled: Payment time limit expired',
                    'cancelled_at'        => $now,
                    'updated_at'          => $now,
                ]);

            // 3. Update status pembayaran terkait menjadi expired
            DB::table('payments')
                ->whereIn('booking_id', $expiredBookingIds)
                ->where('status', 'pending')
                ->update([
                    'status'     => 'expired',
                    'updated_at' => $now,
                ]);

            return $count;
        });
    }

    /**
     * Paginasi booking untuk dashboard staff/owner (Anti N+1)
     */
    public static function getPaginatedBookings(int $tenantId, ?string $search = null, ?string $status = null, ?string $date = null, int $perPage = 10)
    {
        $query = static::select('bookings.id', 'bookings.ulid', 'bookings.tenant_id', 'bookings.venue_id', 'bookings.resource_id', 'bookings.customer_id', 'bookings.booking_code', 'bookings.start_at', 'bookings.end_at', 'bookings.total_amount', 'bookings.status', 'bookings.payment_status', 'bookings.created_at')
            ->with(['venue:id,ulid,name', 'resource:id,ulid,name', 'customer:id,ulid,name,phone'])
            ->where('bookings.tenant_id', $tenantId);

        if (!empty($search)) {
            $query->where(function ($sub) use ($search) {
                $sub->where('bookings.booking_code', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('name', 'like', "%{$search}%")
                          ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if (!empty($status)) {
            $query->where('bookings.status', $status);
        }

        if (!empty($date)) {
            $query->whereDate('bookings.start_at', $date);
        }

        return $query->orderByDesc('bookings.start_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Dapatkan detail booking tunggal dengan lean eager load
     */
    public static function findOptimized(int|string $idOrUlid): ?self
    {
        $column = is_numeric($idOrUlid) ? 'bookings.id' : 'bookings.ulid';
        return static::select('bookings.*')
            ->with([
                'venue:id,ulid,name,phone,address,city',
                'resource:id,ulid,name,code,capacity,image',
                'customer:id,ulid,name,phone,email',
                'addons:id,ulid,booking_id,addon_name,price,quantity,subtotal',
                'payments:id,ulid,tenant_id,booking_id,payment_number,payment_type,payment_method,amount,status,payment_proof,paid_at,confirmed_at',
            ])
            ->where($column, $idOrUlid)
            ->first();
    }

    /**
     * Cari booking berdasarkan kode booking publik
     */
    public static function findByBookingCode(string $bookingCode): ?self
    {
        return static::select('bookings.*')
            ->with([
                'tenant:id,name,slug,logo,phone,email,address',
                'venue:id,name,phone,address,city',
                'resource:id,name,code,capacity,image',
                'customer:id,name,phone,email',
                'addons:id,booking_id,addon_name,price,quantity,subtotal',
                'payments:id,tenant_id,booking_id,payment_number,payment_type,payment_method,amount,status,payment_proof,paid_at,confirmed_at',
            ])
            ->where('booking_code', $bookingCode)
            ->first();
    }

    /**
     * ULTRA-FAST CALENDAR MATRIX (RAW QUERY JOIN)
     * Mengembalikan mapping matrix slot ketersediaan per lapangan pada tanggal tertentu
     */
    public static function getCalendarMatrix(int $tenantId, int $venueId, string $date): array
    {
        // 1. Ambil semua resource aktif di venue ini
        $resources = DB::table('resources')
            ->join('resource_types', 'resource_types.id', '=', 'resources.resource_type_id')
            ->where('resources.tenant_id', $tenantId)
            ->where('resources.venue_id', $venueId)
            ->where('resources.is_active', 1)
            ->whereNull('resources.deleted_at')
            ->select('resources.id', 'resources.name', 'resources.code', 'resource_types.default_slot_minutes')
            ->orderBy('resources.name')
            ->get();

        // 2. Ambil semua booking di tanggal ini (Hanya booking yang bukan cancelled/no_show)
        $startOfDay = Carbon::parse($date)->startOfDay();
        $endOfDay   = Carbon::parse($date)->endOfDay();
        $now        = Carbon::now();

        $bookings = DB::table('bookings')
            ->join('customers', 'customers.id', '=', 'bookings.customer_id')
            ->where('bookings.tenant_id', $tenantId)
            ->where('bookings.venue_id', $venueId)
            ->whereNull('bookings.deleted_at')
            ->whereNotIn('bookings.status', ['cancelled', 'no_show'])
            ->where(function ($holdCheck) use ($now) {
                $holdCheck->where('bookings.status', '!=', 'pending')
                          ->orWhere(function ($hold) use ($now) {
                              $hold->where('bookings.status', 'pending')
                                   ->where('bookings.hold_expires_at', '>', $now);
                          });
            })
            ->where('bookings.start_at', '<', $endOfDay)
            ->where('bookings.end_at', '>', $startOfDay)
            ->select(
                'bookings.id',
                'bookings.resource_id',
                'bookings.booking_code',
                'bookings.start_at',
                'bookings.end_at',
                'bookings.status',
                'bookings.payment_status',
                'customers.name as customer_name',
                'customers.phone as customer_phone'
            )
            ->get();

        return [
            'resources' => $resources,
            'bookings'  => $bookings,
            'date'      => $date,
        ];
    }
}
