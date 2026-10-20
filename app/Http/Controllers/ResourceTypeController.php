<?php

namespace App\Http\Controllers;

use App\Models\ResourceType;
use App\Traits\HasAutoPermissions;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class ResourceTypeController extends Controller implements HasMiddleware
{
    use HasAutoPermissions;

    public function index(Request $request)
    {
        $tenantId = current_tenant_id() ?? 1;
        $search   = $request->query('search');

        $types = ResourceType::getPaginatedData($tenantId, $search, 10);

        return view('admin.resource-types.index', compact('types', 'search'));
    }

    public function create()
    {
        return view('admin.resource-types.create');
    }

    public function store(Request $request)
    {
        $tenantId = current_tenant_id() ?? 1;

        $validated = $request->validate([
            'name'                 => 'required|string|max:100',
            'icon'                 => 'nullable|string|max:50',
            'default_slot_minutes' => 'required|integer|min:15|max:480',
            'min_duration_minutes' => 'required|integer|min:15|max:480',
            'max_duration_minutes' => 'required|integer|min:15|max:1440',
            'buffer_minutes'       => 'nullable|integer|min:0|max:120',
            'is_active'            => 'nullable|boolean',
        ], [
            'name.required'                 => 'Nama jenis olahraga wajib diisi.',
            'default_slot_minutes.required' => 'Durasi default per slot wajib diisi.',
            'min_duration_minutes.required' => 'Durasi minimal booking wajib diisi.',
            'max_duration_minutes.required' => 'Durasi maksimal booking wajib diisi.',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['buffer_minutes'] = $validated['buffer_minutes'] ?? 0;

        ResourceType::createForTenant($tenantId, $validated);

        return redirect()->route('resource-types.index')
            ->with('success', "Jenis olahraga '{$validated['name']}' berhasil ditambahkan!");
    }

    public function edit(string $ulid)
    {
        $tenantId = current_tenant_id() ?? 1;
        $type = ResourceType::findByUlidForTenant($ulid, $tenantId);

        if (!$type) {
            abort(404, 'Jenis olahraga tidak ditemukan.');
        }

        return view('admin.resource-types.edit', compact('type'));
    }

    public function update(Request $request, string $ulid)
    {
        $tenantId = current_tenant_id() ?? 1;
        $type = ResourceType::findByUlidForTenant($ulid, $tenantId);

        if (!$type) {
            abort(404, 'Jenis olahraga tidak ditemukan.');
        }

        // Cegah tenant mengedit template global platform kecuali jika mereka superadmin
        if ($type->tenant_id === null && authUser()?->role?->slug !== 'superadmin') {
            abort(403, 'Anda tidak dapat mengubah jenis olahraga template global bawaan platform.');
        }

        $validated = $request->validate([
            'name'                 => 'required|string|max:100',
            'icon'                 => 'nullable|string|max:50',
            'default_slot_minutes' => 'required|integer|min:15|max:480',
            'min_duration_minutes' => 'required|integer|min:15|max:480',
            'max_duration_minutes' => 'required|integer|min:15|max:1440',
            'buffer_minutes'       => 'nullable|integer|min:0|max:120',
            'is_active'            => 'nullable|boolean',
        ], [
            'name.required'                 => 'Nama jenis olahraga wajib diisi.',
            'default_slot_minutes.required' => 'Durasi default per slot wajib diisi.',
            'min_duration_minutes.required' => 'Durasi minimal booking wajib diisi.',
            'max_duration_minutes.required' => 'Durasi maksimal booking wajib diisi.',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['buffer_minutes'] = $validated['buffer_minutes'] ?? 0;

        $type->updateForTenant($validated);

        return redirect()->route('resource-types.index')
            ->with('success', "Jenis olahraga '{$type->name}' berhasil diperbarui!");
    }

    public function destroy(string $ulid)
    {
        $tenantId = current_tenant_id() ?? 1;
        $type = ResourceType::findByUlidForTenant($ulid, $tenantId);

        if (!$type) {
            abort(404, 'Jenis olahraga tidak ditemukan.');
        }

        if ($type->tenant_id === null && authUser()?->role?->slug !== 'superadmin') {
            return back()->with('error', 'Template jenis olahraga global platform tidak dapat dihapus.');
        }

        try {
            $type->deleteSafely();
            return redirect()->route('resource-types.index')
                ->with('success', "Jenis olahraga '{$type->name}' berhasil dihapus!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
