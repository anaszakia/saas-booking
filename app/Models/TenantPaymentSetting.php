<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\HasAuditColumns;
use App\Traits\HasUlid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class TenantPaymentSetting extends Model
{
    use SoftDeletes, HasAuditColumns, BelongsToTenant, HasUlid;

    protected $fillable = [
        'tenant_id',
        'ulid',
        'provider',
        'server_key',
        'client_key',
        'merchant_id',
        'webhook_secret',
        'is_sandbox',
        'is_active',
        'verified_at',
        'bank_transfer_instructions',
    ];

    protected $casts = [
        'is_sandbox'  => 'boolean',
        'is_active'   => 'boolean',
        'verified_at' => 'datetime',
    ];

    public function setServerKeyAttribute(?string $value): void
    {
        $this->attributes['server_key'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getServerKeyAttribute(?string $value): ?string
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function setWebhookSecretAttribute(?string $value): void
    {
        $this->attributes['webhook_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getWebhookSecretAttribute(?string $value): ?string
    {
        try {
            return $value ? Crypt::decryptString($value) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Dapatkan pengaturan pembayaran aktif tenant
     */
    public static function getActiveSetting(int $tenantId, string $provider): ?self
    {
        return static::where('tenant_id', $tenantId)
            ->where('provider', $provider)
            ->where('is_active', true)
            ->first();
    }
}
