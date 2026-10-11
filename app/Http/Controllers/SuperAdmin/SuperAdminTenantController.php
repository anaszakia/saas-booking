<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Traits\HasAutoPermissions;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class SuperAdminTenantController extends Controller implements HasMiddleware
{
    use HasAutoPermissions;

    public static function middleware(): array
    {
        return [
            // Hak akses dikontrol via role superadmin
        ];
    }

    /**
     * Tampilkan seluruh tenant platform dan status subscription
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tenants = Tenant::getPaginatedTenants($search, 10);
        $plans = Plan::getAllActive();

        return view('superadmin.tenants.index', compact('tenants', 'plans', 'search'));
    }

    /**
     * Tampilkan detail tenant, status kuota, dan riwayat paket
     */
    public function show(Tenant $tenant)
    {
        $currentSub = Subscription::getCurrentTenantSubscription($tenant->id);
        $plans = Plan::getAllActive();

        return view('superadmin.tenants.show', compact('tenant', 'currentSub', 'plans'));
    }

    /**
     * Perpanjang langganan tenant secara manual (misal setelah transfer)
     */
    public function extend(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'months'  => 'required|integer|min:1|max:36',
        ]);

        Subscription::extendSubscription($tenant->id, (int) $validated['plan_id'], (int) $validated['months']);

        return redirect()->route('superadmin.tenants.show', $tenant->ulid)
            ->with('success', "Langganan untuk {$tenant->name} berhasil diperpanjang sebanyak {$validated['months']} bulan!");
    }

    /**
     * Ubah status tenant (active / suspended)
     */
    public function toggleStatus(Tenant $tenant)
    {
        $newStatus = $tenant->status === 'active' ? 'suspended' : 'active';
        $tenant->update(['status' => $newStatus]);

        return back()->with('success', "Status tenant {$tenant->name} berhasil diubah menjadi " . strtoupper($newStatus));
    }
}
