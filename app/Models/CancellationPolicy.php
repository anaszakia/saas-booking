<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CancellationPolicy extends Model
{
    use SoftDeletes, HasAuditColumns, BelongsToTenant, HasUlid;

    protected $fillable = [
        'tenant_id',
        'ulid',
        'resource_type_id',
        'hours_before',
        'refund_percentage',
        'is_active',
    ];

    protected $casts = [
        'hours_before'      => 'integer',
        'refund_percentage' => 'decimal:2',
        'is_active'         => 'boolean',
    ];

    /**
     * Cari kebijakan refund yang paling sesuai berdasarkan selisih jam
     */
    public static function getApplicableRefundPercentage(int $tenantId, int $hoursDifference, ?int $typeId = null): float
    {
        $policy = static::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where(function ($q) use ($typeId) {
                if ($typeId) {
                    $q->where('resource_type_id', $typeId)->orWhereNull('resource_type_id');
                } else {
                    $q->whereNull('resource_type_id');
                }
            })
            ->where('hours_before', '<=', $hoursDifference)
            ->orderByDesc('hours_before')
            ->first();

        return (float) ($policy?->refund_percentage ?? 0.0);
    }
}
