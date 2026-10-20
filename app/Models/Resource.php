<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Resource extends Model
{
    use SoftDeletes, HasAuditColumns, BelongsToTenant, HasUlid;

    protected $fillable = [
        'ulid',
        'tenant_id',
        'venue_id',
        'resource_type_id',
        'name',
        'code',
        'capacity',
        'image',
        'attributes',
        'is_active',
    ];

    protected $casts = [
        'attributes' => 'array',
        'is_active'  => 'boolean',
        'capacity'   => 'integer',
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class);
    }

    public function resourceType()
    {
        return $this->belongsTo(ResourceType::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Paginasi resources dengan eager loading kolom spesifik (Anti N+1)
     */
    public static function getPaginatedData(int $tenantId, ?string $search = null, ?int $venueId = null, ?int $typeId = null, int $perPage = 10)
    {
        $query = static::select('resources.id', 'resources.ulid', 'resources.tenant_id', 'resources.venue_id', 'resources.resource_type_id', 'resources.name', 'resources.code', 'resources.capacity', 'resources.image', 'resources.is_active', 'resources.created_at')
            ->with(['venue:id,ulid,name', 'resourceType:id,name,slug'])
            ->where('resources.tenant_id', $tenantId);

        if (!empty($search)) {
            $query->where(function ($sub) use ($search) {
                $sub->where('resources.name', 'like', "%{$search}%")
                    ->orWhere('resources.code', 'like', "%{$search}%");
            });
        }

        if ($venueId) {
            $query->where('resources.venue_id', $venueId);
        }

        if ($typeId) {
            $query->where('resources.resource_type_id', $typeId);
        }

        return $query->orderBy('resources.name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Direct raw query untuk mendapatkan list resource aktif per venue
     */
    public static function getActiveByVenue(int $tenantId, int $venueId): array
    {
        return DB::table('resources')
            ->join('resource_types', 'resource_types.id', '=', 'resources.resource_type_id')
            ->where('resources.tenant_id', $tenantId)
            ->where('resources.venue_id', $venueId)
            ->where('resources.is_active', 1)
            ->whereNull('resources.deleted_at')
            ->select(
                'resources.id',
                'resources.ulid',
                'resources.name',
                'resources.code',
                'resources.capacity',
                'resources.image',
                'resource_types.id as resource_type_id',
                'resource_types.name as type_name',
                'resource_types.icon as type_icon',
                'resource_types.default_slot_minutes'
            )
            ->orderBy('resources.name')
            ->get()
            ->toArray();
    }

    /**
     * Find resource by ID or ULID dengan eager loading lean
     */
    public static function findOptimized(int|string $idOrUlid): ?self
    {
        $column = is_numeric($idOrUlid) ? 'id' : 'ulid';
        return static::select('id', 'ulid', 'tenant_id', 'venue_id', 'resource_type_id', 'name', 'code', 'capacity', 'image', 'attributes', 'is_active', 'created_at')
            ->with(['venue:id,ulid,name', 'resourceType:id,name,slug'])
            ->where($column, $idOrUlid)
            ->first();
    }

    public function getImageUrlAttribute(): string
    {
        return minio_url($this->image);
    }
}
