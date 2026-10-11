<?php

namespace App\Models;

use App\Traits\HasAuditColumns;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Tenant extends Model
{
    use SoftDeletes, HasAuditColumns, HasUlid;

    protected $fillable = [
        'ulid',
        'name',
        'slug',
        'subdomain',
        'logo',
        'email',
        'phone',
        'address',
        'timezone',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function venues()
    {
        return $this->hasMany(Venue::class);
    }

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Cari tenant berdasarkan subdomain (ultra fast raw lookup)
     */
    public static function findBySubdomain(string $subdomain): ?self
    {
        return static::select('id', 'ulid', 'name', 'slug', 'subdomain', 'logo', 'timezone', 'status', 'settings')
            ->where('subdomain', $subdomain)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Cari tenant berdasarkan slug (untuk akses /b/{slug})
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::select('id', 'ulid', 'name', 'slug', 'subdomain', 'logo', 'email', 'phone', 'address', 'timezone', 'status', 'settings')
            ->where('slug', $slug)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Find optimized dengan lean columns
     */
    public static function findOptimized(int|string $idOrUlid): ?self
    {
        $column = is_numeric($idOrUlid) ? 'id' : 'ulid';
        return static::select('id', 'ulid', 'name', 'slug', 'subdomain', 'logo', 'email', 'phone', 'address', 'timezone', 'status', 'settings', 'created_at')
            ->where($column, $idOrUlid)
            ->first();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get paginated tenants untuk SuperAdmin platform
     */
    public static function getPaginatedTenants(?string $search = null, int $perPage = 10)
    {
        $query = static::select('id', 'ulid', 'name', 'slug', 'subdomain', 'phone', 'email', 'status', 'created_at')
            ->withCount(['venues', 'resources'])
            ->with(['subscriptions' => function ($q) {
                $q->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                  ->select('subscriptions.id', 'subscriptions.tenant_id', 'subscriptions.plan_id', 'subscriptions.status', 'subscriptions.ends_at', 'plans.name as plan_name')
                  ->orderByDesc('subscriptions.id')
                  ->limit(1);
            }]);

        if (!empty($search)) {
            $query->where(function ($sub) use ($search) {
                $sub->where('name', 'like', "%{$search}%")
                    ->orWhere('subdomain', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Logo URL helper
     */
    public function getLogoUrlAttribute(): string
    {
        return minio_avatar($this->logo, $this->name);
    }
}
