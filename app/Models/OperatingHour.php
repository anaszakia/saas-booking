<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class OperatingHour extends Model
{
    use SoftDeletes, HasAuditColumns, BelongsToTenant, HasUlid;

    protected $fillable = [
        'tenant_id',
        'ulid',
        'venue_id',
        'resource_id',
        'day_of_week',
        'open_time',
        'close_time',
        'is_closed',
    ];

    protected $casts = [
        'day_of_week' => 'integer',
        'is_closed'   => 'boolean',
    ];

    /**
     * Dapatkan jam operasional untuk hari tertentu (direct query ultra fast)
     */
    public static function getSchedule(int $tenantId, int $dayOfWeek, ?int $venueId = null, ?int $resourceId = null): ?object
    {
        return DB::table('operating_hours')
            ->where('tenant_id', $tenantId)
            ->where('day_of_week', $dayOfWeek)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($venueId, $resourceId) {
                if ($resourceId) {
                    $q->where('resource_id', $resourceId);
                } elseif ($venueId) {
                    $q->where('venue_id', $venueId);
                } else {
                    $q->whereNull('resource_id')->whereNull('venue_id');
                }
            })
            ->select('open_time', 'close_time', 'is_closed')
            ->first();
    }
}
