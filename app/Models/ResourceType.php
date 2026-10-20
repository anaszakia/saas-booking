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
     * Ambil data terpaginasi untuk tenant tertentu (atau default platform jika superadmin)
     */
    public static function getPaginatedData(int $tenantId, ?string $search = null, int $perPage = 10)
    {
        $query = static::select(
            'id', 'ulid', 'tenant_id', 'name', 'slug', 'icon', 
            'default_slot_minutes', 'min_duration_minutes', 'max_duration_minutes', 
            'buffer_minutes', 'is_active', 'created_at'
        )
        ->where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)
              ->orWhereNull('tenant_id');
        })
        ->withCount('resources');

        if (!empty($search)) {
            $query->where('name', 'like', "%{$search}%");
        }

        return $query->orderBy('tenant_id', 'desc')
            ->orderBy('name', 'asc')
            ->paginate($perPage)
            ->withQueryString();
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

    /**
     * Cari ResourceType berdasarkan ID/ULID dengan isolasi tenant
     */
    public static function findByUlidForTenant(string $ulid, int $tenantId): ?self
    {
        return static::select(
            'id', 'ulid', 'tenant_id', 'name', 'slug', 'icon', 
            'default_slot_minutes', 'min_duration_minutes', 'max_duration_minutes', 
            'buffer_minutes', 'is_active'
        )
        ->where('ulid', $ulid)
        ->where(function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId)
              ->orWhereNull('tenant_id');
        })
        ->first();
    }

    /**
     * Simpan data jenis olahraga baru untuk tenant
     */
    public static function createForTenant(int $tenantId, array $data): self
    {
        $slug = \Illuminate\Support\Str::slug($data['name']);
        $baseSlug = $slug;
        $counter = 1;
        while (static::where('tenant_id', $tenantId)->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return static::create([
            'tenant_id'            => $tenantId,
            'name'                 => $data['name'],
            'slug'                 => $slug,
            'icon'                 => $data['icon'] ?? 'ti ti-ball-football',
            'default_slot_minutes' => $data['default_slot_minutes'] ?? 60,
            'min_duration_minutes' => $data['min_duration_minutes'] ?? 60,
            'max_duration_minutes' => $data['max_duration_minutes'] ?? 180,
            'buffer_minutes'       => $data['buffer_minutes'] ?? 0,
            'is_active'            => $data['is_active'] ?? true,
        ]);
    }

    /**
     * Update data jenis olahraga
     */
    public function updateForTenant(array $data): bool
    {
        return $this->update([
            'name'                 => $data['name'],
            'icon'                 => $data['icon'] ?? $this->icon,
            'default_slot_minutes' => $data['default_slot_minutes'] ?? $this->default_slot_minutes,
            'min_duration_minutes' => $data['min_duration_minutes'] ?? $this->min_duration_minutes,
            'max_duration_minutes' => $data['max_duration_minutes'] ?? $this->max_duration_minutes,
            'buffer_minutes'       => $data['buffer_minutes'] ?? $this->buffer_minutes,
            'is_active'            => $data['is_active'] ?? $this->is_active,
        ]);
    }

    /**
     * Hapus jenis olahraga (soft delete) jika tidak sedang digunakan oleh lapangan aktif
     */
    public function deleteSafely(): bool
    {
        return DB::transaction(function () {
            if ($this->resources()->where('is_active', true)->exists()) {
                throw new \Exception('Jenis olahraga tidak dapat dihapus karena masih digunakan oleh unit lapangan/meja yang aktif.');
            }
            return (bool) $this->delete();
        });
    }
}
