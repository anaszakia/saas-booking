<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Addon extends Model
{
    use SoftDeletes, HasAuditColumns, BelongsToTenant, HasUlid;

    protected $fillable = [
        'tenant_id',
        'ulid',
        'name',
        'price',
        'price_type',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'price'     => 'decimal:2',
        'stock'     => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Dapatkan daftar addon aktif per tenant
     */
    public static function getActiveAddons(int $tenantId): array
    {
        return DB::table('addons')
            ->where('tenant_id', $tenantId)
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->select('id', 'ulid', 'name', 'price', 'price_type', 'stock')
            ->orderBy('name')
            ->get()
            ->toArray();
    }
}
