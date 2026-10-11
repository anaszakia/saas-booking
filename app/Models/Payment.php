<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use App\Traits\HasUlid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    use SoftDeletes, HasAuditColumns, BelongsToTenant, HasUlid;

    protected $fillable = [
        'ulid',
        'tenant_id',
        'booking_id',
        'payment_number',
        'payment_type',
        'payment_method',
        'amount',
        'status',
        'external_id',
        'payment_proof',
        'payload',
        'confirmed_by',
        'confirmed_at',
        'paid_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payload'      => 'array',
        'confirmed_at' => 'datetime',
        'paid_at'      => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function confirmer()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Konfirmasi pembayaran manual oleh kasir/admin
     */
    public static function confirmManualPayment(int|string $idOrUlid, int $userId): bool
    {
        return DB::transaction(function () use ($idOrUlid, $userId) {
            $column = is_numeric($idOrUlid) ? 'id' : 'ulid';
            $payment = static::where($column, $idOrUlid)->lockForUpdate()->first();
            if (!$payment || $payment->status === 'success') {
                return false;
            }

            $now = Carbon::now();

            $payment->update([
                'status'       => 'success',
                'confirmed_by' => $userId,
                'confirmed_at' => $now,
                'paid_at'      => $now,
            ]);

            // Update status booking menjadi confirmed & paid
            DB::table('bookings')
                ->where('id', $payment->booking_id)
                ->update([
                    'status'         => 'confirmed',
                    'payment_status' => 'paid',
                    'updated_at'     => $now,
                ]);

            return true;
        });
    }

    /**
     * Konfirmasi pembayaran otomatis dari Webhook Gateway Tenant (Idempotent & Safe)
     */
    public static function processWebhookNotification(string $paymentNumberOrExternalId, string $status, array $payload): bool
    {
        return DB::transaction(function () use ($paymentNumberOrExternalId, $status, $payload) {
            $payment = static::where(function ($q) use ($paymentNumberOrExternalId) {
                $q->where('payment_number', $paymentNumberOrExternalId)
                  ->orWhere('external_id', $paymentNumberOrExternalId);
            })
            ->lockForUpdate()
            ->first();

            if (!$payment) {
                return false;
            }

            if ($payment->status === 'success') {
                return true; // Idempotent
            }

            $now = Carbon::now();

            if ($status === 'success') {
                $payment->update([
                    'status'         => 'success',
                    'paid_at'        => $now,
                    'payment_method' => $payload['payment_type'] ?? $payload['payment_method'] ?? $payment->payment_method,
                    'payload'        => $payload,
                ]);

                // Update booking menjadi confirmed & paid
                DB::table('bookings')
                    ->where('id', $payment->booking_id)
                    ->update([
                        'status'         => 'confirmed',
                        'payment_status' => 'paid',
                        'updated_at'     => $now,
                    ]);
            } elseif ($status === 'failed' || $status === 'expired') {
                $payment->update([
                    'status'  => 'failed',
                    'payload' => $payload,
                ]);
            }

            return true;
        });
    }

    public function getPaymentProofUrlAttribute(): string
    {
        return minio_url($this->payment_proof);
    }
}
