<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Traits\HasAutoPermissions;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Str;

class SuperAdminPlanController extends Controller implements HasMiddleware
{
    use HasAutoPermissions;

    public static function middleware(): array
    {
        return [];
    }

    /**
     * Daftar paket langganan SaaS
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $plans = Plan::getPaginatedPlans($search, 10);

        return view('superadmin.plans.index', compact('plans', 'search'));
    }

    /**
     * Form tambah paket
     */
    public function create()
    {
        return view('superadmin.plans.create');
    }

    /**
     * Simpan paket baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'price'         => 'required|numeric|min:0',
            'max_resources' => 'required|integer|min:1',
            'max_staff'     => 'required|integer|min:1',
            'is_active'     => 'nullable|boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['features'] = [
            'online_booking'    => $request->boolean('features.online_booking'),
            'calendar_schedule' => $request->boolean('features.calendar_schedule'),
            'export_reports'    => $request->boolean('features.export_reports'),
            'whatsapp_reminder' => $request->boolean('features.whatsapp_reminder'),
            'custom_domain'     => $request->boolean('features.custom_domain'),
        ];

        Plan::create($validated);

        return redirect()->route('superadmin.plans.index')
            ->with('success', 'Paket langganan baru berhasil ditambahkan!');
    }

    /**
     * Form edit paket
     */
    public function edit(Plan $plan)
    {
        return view('superadmin.plans.edit', compact('plan'));
    }

    /**
     * Update paket
     */
    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:100',
            'price'         => 'required|numeric|min:0',
            'max_resources' => 'required|integer|min:1',
            'max_staff'     => 'required|integer|min:1',
            'is_active'     => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['features'] = [
            'online_booking'    => $request->boolean('features.online_booking'),
            'calendar_schedule' => $request->boolean('features.calendar_schedule'),
            'export_reports'    => $request->boolean('features.export_reports'),
            'whatsapp_reminder' => $request->boolean('features.whatsapp_reminder'),
            'custom_domain'     => $request->boolean('features.custom_domain'),
        ];

        $plan->update($validated);

        return redirect()->route('superadmin.plans.index')
            ->with('success', 'Paket langganan berhasil diperbarui!');
    }

    /**
     * Hapus paket
     */
    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('superadmin.plans.index')
            ->with('success', 'Paket langganan berhasil dinonaktifkan/dihapus!');
    }
}
