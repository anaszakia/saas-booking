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
     * Form tambah tenant baru oleh SuperAdmin
     */
    public function create()
    {
        $plans = Plan::getAllActive();

        return view('superadmin.tenants.create', compact('plans'));
    }

    /**
     * Simpan tenant baru dan akun owner oleh SuperAdmin
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_name'     => 'required|string|max:100',
            'owner_name'      => 'required|string|max:100',
            'email'           => 'required|email|max:150|unique:users,email',
            'phone'           => 'nullable|string|max:20',
            'password'        => 'required|string|min:8|confirmed',
            'plan_id'         => 'nullable|exists:plans,id',
            'duration_months' => 'nullable|integer|min:0|max:36',
            'address'         => 'nullable|string|max:255',
        ], [
            'tenant_name.required' => 'Nama bisnis / venue tenant wajib diisi.',
            'owner_name.required'  => 'Nama penanggung jawab (owner) wajib diisi.',
            'email.required'       => 'Alamat email wajib diisi.',
            'email.email'          => 'Format email tidak valid.',
            'email.unique'         => 'Email ini sudah digunakan oleh akun lain.',
            'password.required'    => 'Password wajib diisi.',
            'password.min'         => 'Password minimal 8 karakter.',
            'password.confirmed'   => 'Konfirmasi password tidak cocok.',
        ]);

        $result = Tenant::registerNewTenantWithOwner(
            [
                'name'            => $validated['tenant_name'],
                'address'         => $validated['address'] ?? null,
                'plan_id'         => $validated['plan_id'] ?? null,
                'duration_months' => $validated['duration_months'] ?? 0,
            ],
            [
                'name'     => $validated['owner_name'],
                'email'    => $validated['email'],
                'password' => $validated['password'],
                'phone'    => $validated['phone'] ?? null,
            ]
        );

        return redirect()->route('superadmin.tenants.show', $result['tenant']->ulid)
            ->with('success', "Tenant {$result['tenant']->name} dan akun Owner berhasil dibuat!");
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
