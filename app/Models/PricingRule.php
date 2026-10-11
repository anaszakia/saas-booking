<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use App\Traits\HasUlid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class PricingRule extends Model
{
    use SoftDeletes, HasAuditColumns, BelongsToTenant, HasUlid;

    protected $fillable = [
        'ulid',
        'tenant_id',
        'venue_id',
        'resource_type_id',
        'resource_id',
        'name',
        'day_type',
        'start_time',
        'end_time',
        'price_per_slot',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'price_per_slot' => 'decimal:2',
        'priority'       => 'integer',
        'is_active'      => 'boolean',
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function resourceType()
    {
        return $this->belongsTo(ResourceType::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    /**
     * Paginasi Pricing Rules dengan lean eager load
     */
    public static function getPaginatedData(int $tenantId, ?string $search = null, int $perPage = 10)
    {
        $query = static::select('id', 'ulid', 'tenant_id', 'venue_id', 'resource_type_id', 'resource_id', 'name', 'day_type', 'start_time', 'end_time', 'price_per_slot', 'priority', 'is_active', 'created_at')
            ->with(['venue:id,ulid,name', 'resourceType:id,name', 'resource:id,ulid,name'])
            ->where('tenant_id', $tenantId);

        if (!empty($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->orderByDesc('priority')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Hitung harga per slot menggunakan direct database lookup & priority match
     */
    public static function calculatePrice(int $tenantId, int $resourceId, string $startAt): float
    {
        $dt = Carbon::parse($startAt);
        $timeStr = $dt->format('H:i:s');
        $isWeekend = $dt->isWeekend();
        $dayType = $isWeekend ? 'weekend' : 'weekday';

        $resource = DB::table('resources')
            ->where('id', $resourceId)
            ->select('venue_id', 'resource_type_id')
            ->first();

        $venueId = $resource?->venue_id;
        $typeId  = $resource?->resource_type_id;

        // Ambil rule yang cocok, urutkan dari prioritas tertinggi
        $rule = DB::table('pricing_rules')
            ->where('tenant_id', $tenantId)
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($dayType) {
                $q->where('day_type', 'all')
                  ->orWhere('day_type', $dayType);
            })
            ->where(function ($matchTarget) use ($resourceId, $venueId, $typeId) {
                $matchTarget->where('resource_id', $resourceId)
                            ->orWhere('venue_id', $venueId)
                            ->orWhere('resource_type_id', $typeId)
                            ->orWhere(function ($all) {
                                $all->whereNull('resource_id')
                                    ->whereNull('venue_id')
                                    ->whereNull('resource_type_id');
                            });
            })
            ->where(function ($timeRange) use ($timeStr) {
                $timeRange->where(function ($nullRange) {
                    $nullRange->whereNull('start_time')->whereNull('end_time');
                })->orWhere(function ($between) use ($timeStr) {
                    $between->where('start_time', '<=', $timeStr)
                            ->where('end_time', '>', $timeStr);
                });
            })
            ->orderByDesc('priority')
            ->select('price_per_slot')
            ->first();

        return (float) ($rule?->price_per_slot ?? 0);
    }
}
