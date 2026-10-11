<?php

namespace App\Services\Payment;

use App\Models\TenantPaymentSetting;
use App\Services\Payment\Adapters\MidtransSnapAdapter;
use App\Services\Payment\Adapters\XenditInvoiceAdapter;
use App\Services\Payment\Contracts\PaymentGatewayInterface;

class PaymentGatewayManager
{
    /**
     * Dapatkan adapter gateway berdasarkan nama provider
     */
    public static function getAdapter(string $provider): PaymentGatewayInterface
    {
        return match (strtolower($provider)) {
            'xendit'   => new XenditInvoiceAdapter(),
            default    => new MidtransSnapAdapter(),
        };
    }

    /**
     * Dapatkan kredensial payment gateway resmi PLATFORM (Super Admin)
     */
    public static function getPlatformCredentials(): array
    {
        $dbSetting = TenantPaymentSetting::whereNull('tenant_id')
            ->where('is_active', true)
            ->first();

        if ($dbSetting) {
            return [
                'provider'       => $dbSetting->provider,
                'server_key'     => $dbSetting->server_key,
                'client_key'     => $dbSetting->client_key,
                'merchant_id'    => $dbSetting->merchant_id,
                'webhook_secret' => $dbSetting->webhook_secret ?? $dbSetting->server_key,
                'is_sandbox'     => (bool) $dbSetting->is_sandbox,
            ];
        }

        return [
            'provider'       => env('PLATFORM_PAYMENT_PROVIDER', 'midtrans'),
            'server_key'     => env('PLATFORM_MIDTRANS_SERVER_KEY', env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-demoKey123')),
            'client_key'     => env('PLATFORM_MIDTRANS_CLIENT_KEY', env('MIDTRANS_CLIENT_KEY', 'SB-Mid-client-demoKey123')),
            'merchant_id'    => env('PLATFORM_MIDTRANS_MERCHANT_ID', ''),
            'webhook_secret' => env('PLATFORM_WEBHOOK_SECRET', env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-demoKey123')),
            'is_sandbox'     => (bool) env('PLATFORM_PAYMENT_SANDBOX', true),
        ];
    }

    /**
     * Dapatkan kredensial payment gateway resmi TENANT
     */
    public static function getTenantCredentials(int $tenantId, ?string $preferredProvider = null): ?array
    {
        $setting = TenantPaymentSetting::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->when($preferredProvider, fn($q) => $q->where('provider', $preferredProvider))
            ->first();

        if (!$setting) {
            return null;
        }

        return [
            'provider'       => $setting->provider,
            'server_key'     => $setting->server_key,
            'client_key'     => $setting->client_key,
            'merchant_id'    => $setting->merchant_id,
            'webhook_secret' => $setting->webhook_secret ?? $setting->server_key,
            'is_sandbox'     => (bool) $setting->is_sandbox,
        ];
    }
}
