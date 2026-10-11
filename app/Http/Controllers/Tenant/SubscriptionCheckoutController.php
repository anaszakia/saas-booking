<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SubscriptionInvoice;
use App\Services\Payment\PaymentGatewayManager;
use Illuminate\Http\Request;

class SubscriptionCheckoutController extends Controller
{
    /**
     * Halaman pemilihan paket dan checkout perpanjangan SaaS
     */
    public function index(Request $request)
    {
        $tenantId = current_tenant_id();

        // Jika superadmin (belum memiliki tenant_id terikat), izinkan memilih tenant atau default ke tenant pertama
        $allTenants = collect();
        if (!$tenantId && auth()->user()?->roles()->where('slug', 'superadmin')->exists()) {
            $allTenants = \App\Models\Tenant::all();
            $tenantId = $request->query('tenant_id') ? (int) $request->query('tenant_id') : $allTenants->first()?->id;
            if ($tenantId) {
                session(['tenant_id' => $tenantId]);
            }
        }

        $currentSub = $tenantId ? Subscription::getCurrentTenantSubscription($tenantId) : null;
        $plans = Plan::getAllActive();
        $invoices = $tenantId ? SubscriptionInvoice::getTenantInvoices($tenantId, 5) : collect();

        return view('tenants.subscription.checkout', compact('currentSub', 'plans', 'invoices', 'allTenants', 'tenantId'));
    }

    /**
     * Proses pembuatan tagihan langganan & request token gateway platform
     */
    public function store(Request $request)
    {
        $tenantId = current_tenant_id();

        if (!$tenantId && $request->filled('tenant_id')) {
            $tenantId = (int) $request->input('tenant_id');
        }

        if (!$tenantId) {
            $firstTenant = \App\Models\Tenant::first();
            $tenantId = $firstTenant?->id;
        }

        if (!$tenantId) {
            return back()->with('error', 'Silakan pilih atau daftarkan tenant terlebih dahulu.');
        }

        $validated = $request->validate([
            'plan_id'  => 'required|exists:plans,id',
            'months'   => 'required|integer|in:1,3,6,12',
            'provider' => 'required|in:midtrans,xendit',
        ]);

        $invoice = SubscriptionInvoice::createInvoice($tenantId, (int) $validated['plan_id'], (int) $validated['months'], $validated['provider']);

        // Request payment gateway token ke akun Platform Super Admin
        $credentials = PaymentGatewayManager::getPlatformCredentials();
        $adapter = PaymentGatewayManager::getAdapter($validated['provider']);

        $gatewayResponse = $adapter->createTransaction([
            'order_id'        => $invoice->invoice_number,
            'amount'          => $invoice->amount,
            'item_id'         => 'PLAN-' . $invoice->plan_id,
            'item_name'       => 'Langganan ' . $invoice->plan->name . ' (' . $invoice->months . ' Bulan)',
            'customer_name'   => auth()->user()?->name ?? 'Pemilik Venue',
            'customer_email'  => auth()->user()?->email ?? 'owner@venue.com',
            'customer_phone'  => auth()->user()?->phone ?? '081234567890',
        ], $credentials);

        if (!$gatewayResponse['success']) {
            return back()->with('error', 'Gagal menghubungkan ke gateway platform: ' . ($gatewayResponse['error'] ?? 'Silakan coba lagi.'));
        }

        $invoice->update([
            'snap_token'   => $gatewayResponse['snap_token'] ?? null,
            'checkout_url' => $gatewayResponse['redirect_url'] ?? null,
            'external_id'  => $gatewayResponse['invoice_id'] ?? $invoice->invoice_number,
        ]);

        return redirect()->route('tenant.subscription.pay', $invoice->ulid);
    }

    /**
     * Halaman pembayaran interaktif invoice langganan
     */
    public function pay(SubscriptionInvoice $invoice)
    {
        $platformCreds = PaymentGatewayManager::getPlatformCredentials();

        return view('tenants.subscription.pay', compact('invoice', 'platformCreds'));
    }
}
