<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Closure extends Model
{
    use SoftDeletes, HasAuditColumns, BelongsToTenant, HasUlid;

    protected $fillable = [
        'tenant_id',
        'ulid',
        'venue_id',
        'resource_id',
        'name',
        'start_at',
        'end_at',
        'reason',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
    ];

    /**
     * Cek apakah ada penutupan/libur yang bertabrakan dengan rentang waktu booking
     */
    public static function hasClosureConflict(int $tenantId, int $resourceId, string $startAt, string $endAt): bool
    {
        $venueId = DB::table('resources')->where('id', $resourceId)->value('venue_id');

        return DB::table('closures')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->where(function ($q) use ($venueId, $resourceId) {
                $q->where('resource_id', $resourceId)
                  ->orWhere('venue_id', $venueId)
                  ->orWhere(function ($all) {
                      $all->whereNull('venue_id')->whereNull('resource_id');
                  });
            })
            ->where(function ($time) use ($startAt, $endAt) {
                $time->where('start_at', '<', $endAt)
                     ->where('end_at', '>', $startAt);
            })
            ->exists();
    }
}
