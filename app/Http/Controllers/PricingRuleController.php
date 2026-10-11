<?php

namespace App\Http\Controllers;

use App\Models\PricingRule;
use App\Models\Resource;
use App\Models\ResourceType;
use App\Models\Venue;
use App\Traits\HasAutoPermissions;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class PricingRuleController extends Controller implements HasMiddleware
{
    use HasAutoPermissions;

    public function index(Request $request)
    {
        $tenantId = current_tenant_id() ?? 1;
        $rules    = PricingRule::getPaginatedData($tenantId, $request->query('search'), 10);

        return view('admin.pricing-rules.index', compact('rules'));
    }

    public function create()
    {
        $tenantId = current_tenant_id() ?? 1;
        $venues   = Venue::getDropdownList($tenantId);
        $types    = ResourceType::getAllActive($tenantId);
        $resources = Resource::where('tenant_id', $tenantId)->select('id', 'name')->get();

        return view('admin.pricing-rules.create', compact('venues', 'types', 'resources'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:150',
            'venue_id'         => 'nullable|exists:venues,id',
            'resource_type_id' => 'nullable|exists:resource_types,id',
            'resource_id'      => 'nullable|exists:resources,id',
            'day_type'         => 'required|in:all,weekday,weekend',
            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i',
            'price_per_slot'   => 'required|numeric|min:0',
            'priority'         => 'required|integer|min:0|max:100',
            'is_active'        => 'nullable|boolean',
        ]);

        $tenantId = current_tenant_id() ?? 1;

        PricingRule::create([
            'tenant_id'        => $tenantId,
            'name'             => $request->name,
            'venue_id'         => $request->venue_id,
            'resource_type_id' => $request->resource_type_id,
            'resource_id'      => $request->resource_id,
            'day_type'         => $request->day_type,
            'start_time'       => $request->start_time ? $request->start_time . ':00' : null,
            'end_time'         => $request->end_time ? $request->end_time . ':00' : null,
            'price_per_slot'   => $request->price_per_slot,
            'priority'         => $request->priority,
            'is_active'        => $request->boolean('is_active', true),
        ]);

        return redirect()->route('pricing-rules.index')
            ->with('success', 'Aturan tarif berhasil ditambahkan!');
    }

    public function edit(PricingRule $pricingRule)
    {
        $tenantId  = current_tenant_id() ?? 1;
        $venues    = Venue::getDropdownList($tenantId);
        $types     = ResourceType::getAllActive($tenantId);
        $resources = Resource::where('tenant_id', $tenantId)->select('id', 'name')->get();

        return view('admin.pricing-rules.edit', compact('pricingRule', 'venues', 'types', 'resources'));
    }

    public function update(Request $request, PricingRule $pricingRule)
    {
        $request->validate([
            'name'             => 'required|string|max:150',
            'venue_id'         => 'nullable|exists:venues,id',
            'resource_type_id' => 'nullable|exists:resource_types,id',
            'resource_id'      => 'nullable|exists:resources,id',
            'day_type'         => 'required|in:all,weekday,weekend',
            'start_time'       => 'nullable|date_format:H:i',
            'end_time'         => 'nullable|date_format:H:i',
            'price_per_slot'   => 'required|numeric|min:0',
            'priority'         => 'required|integer|min:0|max:100',
            'is_active'        => 'nullable|boolean',
        ]);

        $pricingRule->update([
            'name'             => $request->name,
            'venue_id'         => $request->venue_id,
            'resource_type_id' => $request->resource_type_id,
            'resource_id'      => $request->resource_id,
            'day_type'         => $request->day_type,
            'start_time'       => $request->start_time ? (strlen($request->start_time) === 5 ? $request->start_time . ':00' : $request->start_time) : null,
            'end_time'         => $request->end_time ? (strlen($request->end_time) === 5 ? $request->end_time . ':00' : $request->end_time) : null,
            'price_per_slot'   => $request->price_per_slot,
            'priority'         => $request->priority,
            'is_active'        => $request->boolean('is_active', true),
        ]);

        return redirect()->route('pricing-rules.index')
            ->with('success', 'Aturan tarif berhasil diupdate!');
    }

    public function destroy(PricingRule $pricingRule)
    {
        $pricingRule->delete();

        return redirect()->route('pricing-rules.index')
            ->with('success', 'Aturan tarif berhasil dihapus!');
    }
}
