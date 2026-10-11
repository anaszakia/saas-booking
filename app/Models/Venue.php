<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Venue extends Model
{
    use SoftDeletes, HasAuditColumns, BelongsToTenant, HasUlid;

    protected $fillable = [
        'ulid',
        'tenant_id',
        'name',
        'slug',
        'phone',
        'address',
        'latitude',
        'longitude',
        'city',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'latitude'  => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    /**
     * Paginasi venues dengan lean column selection & index search
     */
    public static function getPaginatedData(int $tenantId, ?string $search = null, int $perPage = 10)
    {
        $query = static::select('id', 'ulid', 'tenant_id', 'name', 'slug', 'phone', 'city', 'image', 'is_active', 'created_at')
            ->where('tenant_id', $tenantId);

        if (!empty($search)) {
            $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Detail venue dengan lean resources eager load
     */
    public static function findOptimized(int|string $idOrUlid): ?self
    {
        $column = is_numeric($idOrUlid) ? 'id' : 'ulid';
        return static::select('id', 'ulid', 'tenant_id', 'name', 'slug', 'phone', 'address', 'latitude', 'longitude', 'city', 'image', 'is_active', 'created_at')
            ->with(['resources:id,ulid,tenant_id,venue_id,resource_type_id,name,code,capacity,is_active'])
            ->where($column, $idOrUlid)
            ->first();
    }

    /**
     * Direct query untuk dropdown venue
     */
    public static function getDropdownList(int $tenantId): array
    {
        return DB::table('venues')
            ->where('tenant_id', $tenantId)
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->pluck('name', 'id')
            ->toArray();
    }

    public function getImageUrlAttribute(): string
    {
        return minio_url($this->image);
    }
}
