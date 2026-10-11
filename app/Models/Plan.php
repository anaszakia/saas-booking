<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Plan extends Model
{
    use SoftDeletes, HasAuditColumns, HasUlid;

    protected $fillable = [
        'ulid',
        'name',
        'slug',
        'price',
        'max_resources',
        'max_staff',
        'features',
        'is_active',
    ];

    protected $casts = [
        'price'         => 'decimal:2',
        'max_resources' => 'integer',
        'max_staff'     => 'integer',
        'features'      => 'array',
        'is_active'     => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Dapatkan semua paket yang sedang aktif
     */
    public static function getAllActive()
    {
        return static::select('id', 'ulid', 'name', 'slug', 'price', 'max_resources', 'max_staff', 'features', 'is_active')
            ->where('is_active', true)
            ->orderBy('price')
            ->get();
    }

    /**
     * Paginated plans untuk Super Admin
     */
    public static function getPaginatedPlans(?string $search = null, int $perPage = 10)
    {
        $query = static::select('id', 'ulid', 'name', 'slug', 'price', 'max_resources', 'max_staff', 'features', 'is_active', 'created_at')
            ->withCount('subscriptions');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('price')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Find optimized dengan ID atau ULID
     */
    public static function findOptimized(int|string $idOrUlid): ?self
    {
        $column = is_numeric($idOrUlid) ? 'id' : 'ulid';
        return static::select('id', 'ulid', 'name', 'slug', 'price', 'max_resources', 'max_staff', 'features', 'is_active')
            ->where($column, $idOrUlid)
            ->first();
    }
}
