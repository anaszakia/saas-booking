<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\TenantPaymentSetting;
use App\Traits\HasAutoPermissions;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class SuperAdminPaymentSettingController extends Controller implements HasMiddleware
{
    use HasAutoPermissions;

    public static function middleware(): array
    {
        return [];
    }

    /**
     * Tampilkan form pengaturan payment gateway resmi platform
     */
    public function index()
    {
        $settings = TenantPaymentSetting::whereNull('tenant_id')->get()->keyBy('provider');

        return view('superadmin.payment-settings.index', compact('settings'));
    }

    /**
     * Simpan / Perbarui pengaturan payment gateway platform
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'provider'                   => 'required|in:midtrans,xendit,manual',
            'server_key'                 => 'nullable|string|max:255',
            'client_key'                 => 'nullable|string|max:255',
            'merchant_id'                => 'nullable|string|max:100',
            'webhook_secret'             => 'nullable|string|max:255',
            'is_sandbox'                 => 'nullable|boolean',
            'is_active'                  => 'nullable|boolean',
            'bank_transfer_instructions' => 'nullable|string',
        ]);

        $setting = TenantPaymentSetting::firstOrNew([
            'tenant_id' => null,
            'provider'  => $validated['provider'],
        ]);

        $setting->is_sandbox = $request->boolean('is_sandbox');
        $setting->is_active = $request->boolean('is_active');
        $setting->bank_transfer_instructions = $validated['bank_transfer_instructions'] ?? null;

        if (!empty($validated['server_key'])) {
            $setting->server_key = $validated['server_key'];
        }
        if (!empty($validated['client_key'])) {
            $setting->client_key = $validated['client_key'];
        }
        if (!empty($validated['merchant_id'])) {
            $setting->merchant_id = $validated['merchant_id'];
        }
        if (!empty($validated['webhook_secret'])) {
            $setting->webhook_secret = $validated['webhook_secret'];
        }

        $setting->save();

        return back()->with('success', 'Konfigurasi Payment Gateway Platform (' . strtoupper($validated['provider']) . ') berhasil disimpan!');
    }
}
