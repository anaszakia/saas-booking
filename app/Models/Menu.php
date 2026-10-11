<?php
namespace App\Models;

use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasUlid;

    protected $fillable = ['ulid', 'name', 'url', 'icon', 'parent_id', 'order', 'is_active'];

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'menu_role');
    }

    /**
     * Get paginated root menus with search & relations (optimized)
     */
    public static function getPaginatedMenus(?string $search = null, int $perPage = 10)
    {
        $query = static::select('menus.id', 'menus.ulid', 'menus.name', 'menus.url', 'menus.icon', 'menus.order', 'menus.is_active', 'menus.parent_id')
            ->with(['parent:id,ulid,name', 'roles:id,ulid,name,slug', 'children.roles:id,ulid,name,slug'])
            ->whereNull('parent_id')
            ->withCount('children');

        if (!empty($search)) {
            $query->where(function ($sub) use ($search) {
                $sub->where('menus.name', 'like', "%{$search}%")
                    ->orWhere('menus.url', 'like', "%{$search}%")
                    ->orWhere('menus.icon', 'like', "%{$search}%")
                    ->orWhereExists(function ($rawQuery) use ($search) {
                        $rawQuery->select(\Illuminate\Support\Facades\DB::raw(1))
                            ->from('menus as sub_menus')
                            ->whereColumn('sub_menus.parent_id', 'menus.id')
                            ->where(function ($childQuery) use ($search) {
                                $childQuery->where('sub_menus.name', 'like', "%{$search}%")
                                           ->orWhere('sub_menus.url', 'like', "%{$search}%")
                                           ->orWhere('sub_menus.icon', 'like', "%{$search}%");
                            });
                    });
            });
        }

        return $query->orderBy('menus.order')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get root menus with children (lean column selection)
     */
    public static function getRootMenusWithChildren()
    {
        return static::select('id', 'ulid', 'name', 'url', 'icon', 'order', 'parent_id')
            ->with(['children' => fn($q) => $q->select('id', 'ulid', 'name', 'url', 'icon', 'order', 'parent_id')->orderBy('order')])
            ->whereNull('parent_id')
            ->orderBy('order')
            ->get();
    }

    /**
     * Get available parent menu options for create/edit
     */
    public static function getParentOptions(?int $exceptId = null)
    {
        return static::select('id', 'name', 'order')
            ->whereNull('parent_id')
            ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
            ->orderBy('order')
            ->get();
    }
}