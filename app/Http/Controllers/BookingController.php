<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PricingRule;
use App\Models\Resource;
use App\Models\Venue;
use App\Traits\HasAutoPermissions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Str;

class BookingController extends Controller implements HasMiddleware
{
    use HasAutoPermissions;

    public function index(Request $request)
    {
        $tenantId = current_tenant_id() ?? 1;

        $bookings = Booking::getPaginatedBookings(
            $tenantId,
            $request->query('search'),
            $request->query('status'),
            $request->query('date'),
            10
        );

        return view('admin.bookings.index', compact('bookings'));
    }

    public function create(Request $request)
    {
        $tenantId = current_tenant_id() ?? 1;
        $venues   = Venue::getDropdownList($tenantId);

        $selectedVenueId = $request->query('venue_id') ? (int) $request->query('venue_id') : array_key_first($venues);
        $resources       = $selectedVenueId ? Resource::getActiveByVenue($tenantId, $selectedVenueId) : [];
        $addons          = Addon::getActiveAddons($tenantId);

        return view('admin.bookings.create', compact('venues', 'selectedVenueId', 'resources', 'addons'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'venue_id'       => 'required|exists:venues,id',
            'resource_id'    => 'required|exists:resources,id',
            'customer_name'  => 'required|string|max:150',
            'customer_phone' => 'required|string|max:30',
            'customer_email' => 'nullable|email|max:150',
            'booking_date'   => 'required|date|after_or_equal:today',
            'start_time'     => 'required|date_format:H:i',
            'duration_hours' => 'required|integer|min:1|max:8',
            'payment_method' => 'required|in:cash,manual_transfer',
            'payment_status' => 'required|in:paid,unpaid',
            'notes'          => 'nullable|string|max:500',
            'addons'         => 'nullable|array',
        ]);

        $tenantId = current_tenant_id() ?? 1;

        // 1. Simpan/Lookup Customer
        $customer = Customer::findOrCreateByPhone($tenantId, [
            'name'  => $request->customer_name,
            'phone' => $request->customer_phone,
            'email' => $request->customer_email,
        ]);

        // 2. Hitung rentang waktu
        $startAt = Carbon::parse($request->booking_date . ' ' . $request->start_time . ':00');
        $endAt   = (clone $startAt)->addHours((int) $request->duration_hours);

        // 3. Kalkulasi Tarif Lapangan per jam
        $slotPrice = PricingRule::calculatePrice($tenantId, $request->resource_id, $startAt->toDateTimeString());
        $subtotal  = $slotPrice * (int) $request->duration_hours;

        // 4. Proses Addons
        $addonItems = [];
        $addonTotal = 0;
        if (!empty($request->addons)) {
            $allActiveAddons = collect(Addon::getActiveAddons($tenantId))->keyBy('id');
            foreach ($request->addons as $addonId => $qty) {
                $qty = (int) $qty;
                if ($qty > 0 && isset($allActiveAddons[$addonId])) {
                    $item = $allActiveAddons[$addonId];
                    $addonItems[] = [
                        'id'       => $item->id,
                        'name'     => $item->name,
                        'price'    => (float) $item->price,
                        'quantity' => $qty,
                    ];
                    $addonTotal += ((float) $item->price * $qty);
                }
            }
        }

        $totalAmount = $subtotal + $addonTotal;

        try {
            // 5. Eksekusi Booking dengan Pessimistic Locking
            $isPaid = $request->payment_status === 'paid';

            $booking = Booking::createBookingWithLock([
                'tenant_id'       => $tenantId,
                'venue_id'        => $request->venue_id,
                'resource_id'     => $request->resource_id,
                'customer_id'     => $customer->id,
                'user_id'         => auth()->id() ?? session('user_id'),
                'start_at'        => $startAt->toDateTimeString(),
                'end_at'          => $endAt->toDateTimeString(),
                'subtotal'        => $subtotal,
                'addon_total'     => $addonTotal,
                'discount_total'  => 0,
                'total_amount'    => $totalAmount,
                'status'          => $isPaid ? 'confirmed' : 'pending',
                'payment_status'  => $isPaid ? 'paid' : 'unpaid',
                'hold_expires_at' => $isPaid ? null : Carbon::now()->addMinutes(15),
                'notes'           => $request->notes,
            ], $addonItems);

            // 6. Buat Record Pembayaran Kasir
            Payment::create([
                'tenant_id'      => $tenantId,
                'booking_id'     => $booking->id,
                'payment_number' => 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                'payment_type'   => 'full_payment',
                'payment_method' => $request->payment_method,
                'amount'         => $totalAmount,
                'status'         => $isPaid ? 'success' : 'pending',
                'confirmed_by'   => $isPaid ? (auth()->id() ?? session('user_id')) : null,
                'confirmed_at'   => $isPaid ? Carbon::now() : null,
                'paid_at'        => $isPaid ? Carbon::now() : null,
            ]);

            return redirect()->route('bookings.show', $booking->id)
                ->with('success', 'Booking berhasil dibuat!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(Booking $booking)
    {
        $booking = Booking::findOptimized($booking->id);

        return view('admin.bookings.show', compact('booking'));
    }

    public function destroy(Booking $booking)
    {
        $booking->update([
            'status'              => 'cancelled',
            'cancellation_reason' => 'Dibatalkan oleh staf dari dashboard',
            'cancelled_at'        => Carbon::now(),
        ]);

        return redirect()->route('bookings.index')
            ->with('success', 'Booking berhasil dibatalkan!');
    }
}
