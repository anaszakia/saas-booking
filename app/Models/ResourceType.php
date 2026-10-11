<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ResourceType extends Model
{
    use SoftDeletes, HasAuditColumns, BelongsToTenant, HasUlid;

    protected $fillable = [
        'tenant_id',
        'ulid',
        'name',
        'slug',
        'icon',
        'default_slot_minutes',
        'min_duration_minutes',
        'max_duration_minutes',
        'buffer_minutes',
        'is_active',
    ];

    protected $casts = [
        'is_active'             => 'boolean',
        'default_slot_minutes'  => 'integer',
        'min_duration_minutes'  => 'integer',
        'max_duration_minutes'  => 'integer',
        'buffer_minutes'        => 'integer',
    ];

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    /**
     * Ambil semua tipe resource aktif untuk tenant atau default sistem
     */
    public static function getAllActive(?int $tenantId = null)
    {
        return static::select('id', 'ulid', 'tenant_id', 'name', 'slug', 'icon', 'default_slot_minutes', 'buffer_minutes')
            ->where(function ($query) use ($tenantId) {
                $query->whereNull('tenant_id');
                if ($tenantId) {
                    $query->orWhere('tenant_id', $tenantId);
                }
            })
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
