<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Customer extends Model
{
    use SoftDeletes, HasAuditColumns, BelongsToTenant, HasUlid;

    protected $fillable = [
        'ulid',
        'tenant_id',
        'name',
        'phone',
        'email',
        'notes',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Cari atau buat customer berdasarkan nomor telepon (unique per tenant)
     */
    public static function findOrCreateByPhone(int $tenantId, array $data): self
    {
        $existing = static::where('tenant_id', $tenantId)
            ->where('phone', $data['phone'])
            ->first();

        if ($existing) {
            if (!empty($data['name']) && $existing->name !== $data['name']) {
                $existing->update(['name' => $data['name']]);
            }
            return $existing;
        }

        return static::create([
            'tenant_id' => $tenantId,
            'name'      => $data['name'],
            'phone'     => $data['phone'],
            'email'     => $data['email'] ?? null,
            'notes'     => $data['notes'] ?? null,
        ]);
    }

    /**
     * Paginasi customer terindeks
     */
    public static function getPaginatedCustomers(int $tenantId, ?string $search = null, int $perPage = 10)
    {
        $query = static::select('id', 'ulid', 'tenant_id', 'name', 'phone', 'email', 'created_at')
            ->where('tenant_id', $tenantId);

        if (!empty($search)) {
            $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }
}
