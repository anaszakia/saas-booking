<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionInvoice extends Model
{
    use SoftDeletes, HasAuditColumns, BelongsToTenant, HasUlid;

    protected $fillable = [
        'ulid',
        'tenant_id',
        'plan_id',
        'invoice_number',
        'months',
        'amount',
        'provider',
        'payment_method',
        'external_id',
        'snap_token',
        'checkout_url',
        'status',
        'paid_at',
        'payload',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'months'  => 'integer',
        'paid_at' => 'datetime',
        'payload' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Generate Invoice Langganan SaaS baru
     */
    public static function createInvoice(int $tenantId, int $planId, int $months, string $provider = 'midtrans'): self
    {
        $plan = Plan::findOptimized($planId);
        if (!$plan) {
            throw new \InvalidArgumentException("Paket tidak valid.");
        }

        $totalAmount = (float) $plan->price * $months;
        $invoiceNumber = 'SUB-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

        return static::create([
            'tenant_id'      => $tenantId,
            'plan_id'        => $plan->id,
            'invoice_number' => $invoiceNumber,
            'months'         => $months,
            'amount'         => $totalAmount,
            'provider'       => $provider,
            'status'         => 'pending',
        ]);
    }

    /**
     * Settle Invoice Langganan via Webhook (Idempotent & Safe)
     */
    public static function settleInvoice(string $invoiceNumberOrExternalId, array $gatewayPayload): bool
    {
        return DB::transaction(function () use ($invoiceNumberOrExternalId, $gatewayPayload) {
            $invoice = static::where(function ($q) use ($invoiceNumberOrExternalId) {
                $q->where('invoice_number', $invoiceNumberOrExternalId)
                  ->orWhere('external_id', $invoiceNumberOrExternalId);
            })
            ->lockForUpdate()
            ->first();

            if (!$invoice) {
                return false;
            }

            if ($invoice->status === 'paid') {
                return true; // Sudah lunas sebelumnya (Idempotent)
            }

            $now = Carbon::now();

            $invoice->update([
                'status'         => 'paid',
                'paid_at'        => $now,
                'payment_method' => $gatewayPayload['payment_type'] ?? $gatewayPayload['payment_method'] ?? $invoice->payment_method,
                'payload'        => $gatewayPayload,
            ]);

            // Otomatis perpanjang masa aktif subscriptions tenant
            Subscription::extendSubscription($invoice->tenant_id, $invoice->plan_id, $invoice->months);

            return true;
        });
    }

    /**
     * Ambil riwayat tagihan tenant (optimized)
     */
    public static function getTenantInvoices(int $tenantId, int $perPage = 10)
    {
        return static::select('subscription_invoices.id', 'subscription_invoices.ulid', 'subscription_invoices.invoice_number', 'subscription_invoices.months', 'subscription_invoices.amount', 'subscription_invoices.provider', 'subscription_invoices.payment_method', 'subscription_invoices.status', 'subscription_invoices.paid_at', 'subscription_invoices.created_at', 'plans.name as plan_name')
            ->join('plans', 'subscription_invoices.plan_id', '=', 'plans.id')
            ->where('subscription_invoices.tenant_id', $tenantId)
            ->orderByDesc('subscription_invoices.id')
            ->paginate($perPage);
    }
}
