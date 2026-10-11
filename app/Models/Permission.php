<?php
namespace App\Models;

use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasUlid;

    protected $fillable = ['ulid', 'name', 'slug'];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }

    /**
     * Get paginated permissions with roles & search (optimized)
     */
    public static function getPaginatedPermissions(?string $search = null, int $perPage = 10)
    {
        $query = static::select('permissions.id', 'permissions.ulid', 'permissions.name', 'permissions.slug', 'permissions.created_at')
            ->with('roles:id,ulid,name,slug');

        if (!empty($search)) {
            $query->where(function ($sub) use ($search) {
                $sub->where('permissions.name', 'like', "%{$search}%")
                    ->orWhere('permissions.slug', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('permissions.name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get permissions grouped by module (lean query)
     */
    public static function getGroupedByModule()
    {
        return static::select('id', 'ulid', 'name', 'slug')
            ->orderBy('slug')
            ->get()
            ->groupBy(fn($p) => explode('.', $p->slug)[0]);
    }
}