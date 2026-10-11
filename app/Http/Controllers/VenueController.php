<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Services\MinioService;
use App\Traits\HasAutoPermissions;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Str;

class VenueController extends Controller implements HasMiddleware
{
    use HasAutoPermissions;

    public function __construct(protected MinioService $minio) {}

    public function index(Request $request)
    {
        $tenantId = current_tenant_id() ?? 1;
        $venues = Venue::getPaginatedData($tenantId, $request->query('search'), 10);

        return view('admin.venues.index', compact('venues'));
    }

    public function create()
    {
        return view('admin.venues.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:150',
            'phone'     => 'nullable|string|max:30',
            'city'      => 'nullable|string|max:100',
            'address'   => 'nullable|string|max:500',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'nullable|boolean',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = minio_upload($request->file('image'), 'venues');
        }

        $tenantId = current_tenant_id() ?? 1;

        Venue::create([
            'tenant_id' => $tenantId,
            'name'      => $request->name,
            'slug'      => Str::slug($request->name) . '-' . Str::random(4),
            'phone'     => $request->phone,
            'city'      => $request->city,
            'address'   => $request->address,
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
            'is_active' => $request->boolean('is_active', true),
            'image'     => $imagePath,
        ]);

        return redirect()->route('venues.index')
            ->with('success', 'Venue / Cabang berhasil ditambahkan!');
    }

    public function show(Venue $venue)
    {
        $venue = Venue::findOptimized($venue->id);

        return view('admin.venues.show', compact('venue'));
    }

    public function edit(Venue $venue)
    {
        return view('admin.venues.edit', compact('venue'));
    }

    public function update(Request $request, Venue $venue)
    {
        $request->validate([
            'name'      => 'required|string|max:150',
            'phone'     => 'nullable|string|max:30',
            'city'      => 'nullable|string|max:100',
            'address'   => 'nullable|string|max:500',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_active' => 'nullable|boolean',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = [
            'name'      => $request->name,
            'phone'     => $request->phone,
            'city'      => $request->city,
            'address'   => $request->address,
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
            'is_active' => $request->boolean('is_active', true),
        ];

        if ($request->hasFile('image')) {
            $data['image'] = minio_replace($venue->image, $request->file('image'), 'venues');
        }

        if ($request->has('remove_image') && $venue->image) {
            minio_delete($venue->image);
            $data['image'] = null;
        }

        $venue->update($data);

        return redirect()->route('venues.index')
            ->with('success', 'Venue / Cabang berhasil diupdate!');
    }

    public function destroy(Venue $venue)
    {
        if ($venue->image) {
            minio_delete($venue->image);
        }

        $venue->delete();

        return redirect()->route('venues.index')
            ->with('success', 'Venue / Cabang berhasil dihapus!');
    }
}
