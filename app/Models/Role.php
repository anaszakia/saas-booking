<?php

namespace App\Models;

use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasUlid;

    protected $fillable = ['ulid', 'name', 'slug'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_role');
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'menu_role');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission');
    }

    /**
     * Get paginated roles with user count & search (optimized)
     */
    public static function getPaginatedRoles(?string $search = null, int $perPage = 10)
    {
        $query = static::select('roles.id', 'roles.name', 'roles.slug', 'roles.created_at')
            ->withCount('users');

        if (!empty($search)) {
            $query->where(function ($sub) use ($search) {
                $sub->where('roles.name', 'like', "%{$search}%")
                    ->orWhere('roles.slug', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('roles.name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get all roles ordered by name (lean column selection)
     */
    public static function getAllOrdered()
    {
        return static::select('id', 'name', 'slug')->orderBy('name')->get();
    }

    /**
     * Get or create default user role
     */
    public static function getDefaultUserRole(): self
    {
        return static::firstOrCreate(
            ['slug' => 'user'],
            ['name' => 'User']
        );
    }
}