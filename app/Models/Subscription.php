<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Subscription extends Model
{
    use SoftDeletes, HasAuditColumns, BelongsToTenant, HasUlid;

    protected $fillable = [
        'tenant_id',
        'ulid',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'trial_ends_at',
    ];

    protected $casts = [
        'starts_at'     => 'datetime',
        'ends_at'       => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Dapatkan langganan aktif tenant saat ini (Direct ultra fast query)
     */
    public static function getCurrentTenantSubscription(int $tenantId): ?object
    {
        return DB::table('subscriptions')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->where('subscriptions.tenant_id', $tenantId)
            ->whereNull('subscriptions.deleted_at')
            ->whereNull('plans.deleted_at')
            ->select(
                'subscriptions.id',
                'subscriptions.ulid',
                'subscriptions.tenant_id',
                'subscriptions.plan_id',
                'subscriptions.status',
                'subscriptions.starts_at',
                'subscriptions.ends_at',
                'subscriptions.trial_ends_at',
                'plans.name as plan_name',
                'plans.slug as plan_slug',
                'plans.price as plan_price',
                'plans.max_resources',
                'plans.max_staff',
                'plans.features'
            )
            ->orderByDesc('subscriptions.id')
            ->first();
    }

    /**
     * Pengecekan kilat apakah tenant memiliki akses aktif
     */
    public static function hasActiveAccess(int $tenantId): bool
    {
        $sub = DB::table('subscriptions')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->select('status', 'ends_at', 'trial_ends_at')
            ->orderByDesc('id')
            ->first();

        if (!$sub) {
            return false;
        }

        if (in_array($sub->status, ['active', 'trialing'])) {
            if ($sub->ends_at && Carbon::parse($sub->ends_at)->isPast()) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Perpanjang langganan secara manual oleh Super Admin
     */
    public static function extendSubscription(int $tenantId, int $planId, int $months = 1): self
    {
        return DB::transaction(function () use ($tenantId, $planId, $months) {
            $latest = static::where('tenant_id', $tenantId)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            $startsAt = now();
            $endsAt = now()->addMonths($months);

            if ($latest && $latest->ends_at && Carbon::parse($latest->ends_at)->isFuture()) {
                $startsAt = Carbon::parse($latest->ends_at);
                $endsAt = Carbon::parse($latest->ends_at)->addMonths($months);
            }

            return static::create([
                'tenant_id' => $tenantId,
                'plan_id'   => $planId,
                'status'    => 'active',
                'starts_at' => $startsAt,
                'ends_at'   => $endsAt,
            ]);
        });
    }
}
