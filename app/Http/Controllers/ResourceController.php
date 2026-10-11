<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\Venue;
use App\Services\MinioService;
use App\Traits\HasAutoPermissions;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class ResourceController extends Controller implements HasMiddleware
{
    use HasAutoPermissions;

    public function __construct(protected MinioService $minio) {}

    public function index(Request $request)
    {
        $tenantId = current_tenant_id() ?? 1;

        $resources = Resource::getPaginatedData(
            $tenantId,
            $request->query('search'),
            $request->query('venue_id') ? (int) $request->query('venue_id') : null,
            $request->query('resource_type_id') ? (int) $request->query('resource_type_id') : null,
            10
        );

        $venues = Venue::getDropdownList($tenantId);
        $types  = ResourceType::getAllActive($tenantId);

        return view('admin.resources.index', compact('resources', 'venues', 'types'));
    }

    public function create()
    {
        $tenantId = current_tenant_id() ?? 1;
        $venues   = Venue::getDropdownList($tenantId);
        $types    = ResourceType::getAllActive($tenantId);

        return view('admin.resources.create', compact('venues', 'types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'venue_id'         => 'required|exists:venues,id',
            'resource_type_id' => 'required|exists:resource_types,id',
            'name'             => 'required|string|max:100',
            'code'             => 'nullable|string|max:50',
            'capacity'         => 'required|integer|min:1|max:100',
            'is_active'        => 'nullable|boolean',
            'image'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'attributes'       => 'nullable|array',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = minio_upload($request->file('image'), 'resources');
        }

        $tenantId = current_tenant_id() ?? 1;

        Resource::create([
            'tenant_id'        => $tenantId,
            'venue_id'         => $request->venue_id,
            'resource_type_id' => $request->resource_type_id,
            'name'             => $request->name,
            'code'             => $request->code,
            'capacity'         => $request->capacity,
            'is_active'        => $request->boolean('is_active', true),
            'attributes'       => $request->input('attributes', []),
            'image'            => $imagePath,
        ]);

        return redirect()->route('resources.index')
            ->with('success', 'Lapangan / Meja berhasil ditambahkan!');
    }

    public function show(Resource $resource)
    {
        $resource = Resource::findOptimized($resource->id);

        return view('admin.resources.show', compact('resource'));
    }

    public function edit(Resource $resource)
    {
        $tenantId = current_tenant_id() ?? 1;
        $venues   = Venue::getDropdownList($tenantId);
        $types    = ResourceType::getAllActive($tenantId);
        $resource = Resource::findOptimized($resource->id);

        return view('admin.resources.edit', compact('resource', 'venues', 'types'));
    }

    public function update(Request $request, Resource $resource)
    {
        $request->validate([
            'venue_id'         => 'required|exists:venues,id',
            'resource_type_id' => 'required|exists:resource_types,id',
            'name'             => 'required|string|max:100',
            'code'             => 'nullable|string|max:50',
            'capacity'         => 'required|integer|min:1|max:100',
            'is_active'        => 'nullable|boolean',
            'image'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'attributes'       => 'nullable|array',
        ]);

        $data = [
            'venue_id'         => $request->venue_id,
            'resource_type_id' => $request->resource_type_id,
            'name'             => $request->name,
            'code'             => $request->code,
            'capacity'         => $request->capacity,
            'is_active'        => $request->boolean('is_active', true),
            'attributes'       => $request->input('attributes', []),
        ];

        if ($request->hasFile('image')) {
            $data['image'] = minio_replace($resource->image, $request->file('image'), 'resources');
        }

        if ($request->has('remove_image') && $resource->image) {
            minio_delete($resource->image);
            $data['image'] = null;
        }

        $resource->update($data);

        return redirect()->route('resources.index')
            ->with('success', 'Lapangan / Meja berhasil diupdate!');
    }

    public function destroy(Resource $resource)
    {
        if ($resource->image) {
            minio_delete($resource->image);
        }

        $resource->delete();

        return redirect()->route('resources.index')
            ->with('success', 'Lapangan / Meja berhasil dihapus!');
    }
}
