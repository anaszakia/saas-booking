<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PricingRule;
use App\Models\Resource;
use App\Models\Tenant;
use App\Models\Venue;
use App\Services\MinioService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicBookingController extends Controller
{
    public function __construct(protected MinioService $minio) {}

    /**
     * Halaman Utama Booking Tenant Publik
     */
    public function index(string $slug, Request $request)
    {
        $tenant = Tenant::findBySlug($slug);
        if (!$tenant) {
            abort(404, 'Tempat olahraga tidak ditemukan.');
        }

        $venues   = Venue::getDropdownList($tenant->id);
        $venueId  = $request->query('venue_id') ? (int) $request->query('venue_id') : array_key_first($venues);
        $date     = $request->query('date', Carbon::today()->toDateString());

        $resources = $venueId ? Resource::getActiveByVenue($tenant->id, $venueId) : [];
        $addons    = Addon::getActiveAddons($tenant->id);

        // Generate matrix slot waktu
        $timeSlots = [];
        for ($hour = 8; $hour <= 22; $hour++) {
            $timeSlots[] = sprintf('%02d:00', $hour);
        }

        $matrix = $venueId ? Booking::getCalendarMatrix($tenant->id, $venueId, $date) : ['bookings' => collect()];

        return view('public.booking', compact('tenant', 'venues', 'venueId', 'date', 'resources', 'addons', 'timeSlots', 'matrix'));
    }

    /**
     * Submit Booking Pelanggan Publik (Hold Slot 15 Menit)
     */
    public function store(string $slug, Request $request)
    {
        $tenant = Tenant::findBySlug($slug);
        if (!$tenant) {
            abort(404, 'Tempat olahraga tidak ditemukan.');
        }

        $request->validate([
            'venue_id'       => 'required|exists:venues,id',
            'resource_id'    => 'required|exists:resources,id',
            'customer_name'  => 'required|string|max:150',
            'customer_phone' => 'required|string|max:30',
            'customer_email' => 'nullable|email|max:150',
            'booking_date'   => 'required|date|after_or_equal:today',
            'start_time'     => 'required|date_format:H:i',
            'duration_hours' => 'required|integer|min:1|max:6',
            'notes'          => 'nullable|string|max:500',
            'addons'         => 'nullable|array',
        ]);

        // 1. Register / Lookup Customer ringan
        $customer = Customer::findOrCreateByPhone($tenant->id, [
            'name'  => $request->customer_name,
            'phone' => $request->customer_phone,
            'email' => $request->customer_email,
        ]);

        // 2. Hitung Waktu
        $startAt = Carbon::parse($request->booking_date . ' ' . $request->start_time . ':00');
        $endAt   = (clone $startAt)->addHours((int) $request->duration_hours);

        // 3. Kalkulasi Tarif
        $slotPrice = PricingRule::calculatePrice($tenant->id, $request->resource_id, $startAt->toDateTimeString());
        $subtotal  = $slotPrice * (int) $request->duration_hours;

        // 4. Hitung Addons
        $addonItems = [];
        $addonTotal = 0;
        if (!empty($request->addons)) {
            $allActiveAddons = collect(Addon::getActiveAddons($tenant->id))->keyBy('id');
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
            // 5. Booking dengan Hold 15 Menit & Lock
            $booking = Booking::createBookingWithLock([
                'tenant_id'       => $tenant->id,
                'venue_id'        => $request->venue_id,
                'resource_id'     => $request->resource_id,
                'customer_id'     => $customer->id,
                'user_id'         => null, // Pelanggan mandiri
                'start_at'        => $startAt->toDateTimeString(),
                'end_at'          => $endAt->toDateTimeString(),
                'subtotal'        => $subtotal,
                'addon_total'     => $addonTotal,
                'discount_total'  => 0,
                'total_amount'    => $totalAmount,
                'status'          => 'pending',
                'payment_status'  => 'unpaid',
                'hold_expires_at' => Carbon::now()->addMinutes(15),
                'notes'           => $request->notes,
            ], $addonItems);

            // 6. Buat Record Pembayaran Manual Pending
            Payment::create([
                'tenant_id'      => $tenant->id,
                'booking_id'     => $booking->id,
                'payment_number' => 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                'payment_type'   => 'full_payment',
                'payment_method' => 'manual_transfer',
                'amount'         => $totalAmount,
                'status'         => 'pending',
            ]);

            return redirect()->route('public.invoice', $booking->booking_code)
                ->with('success', 'Slot lapangan berhasil ditahan untuk Anda! Silakan selesaikan pembayaran dalam 15 menit.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Halaman Publik Invoice & Status Pembayaran
     */
    public function invoice(string $bookingCode)
    {
        $booking = Booking::findByBookingCode($bookingCode);
        if (!$booking) {
            abort(404, 'Kode booking tidak ditemukan.');
        }

        $payment = $booking->payments->first();
        $tenantCredentials = \App\Services\Payment\PaymentGatewayManager::getTenantCredentials($booking->tenant_id);

        return view('public.invoice', compact('booking', 'payment', 'tenantCredentials'));
    }

    /**
     * Generate Snap Token untuk Invoice Booking Customer
     */
    public function requestSnapToken(string $bookingCode)
    {
        $booking = Booking::findByBookingCode($bookingCode);
        if (!$booking) {
            return response()->json(['success' => false, 'error' => 'Booking tidak ditemukan'], 404);
        }

        $payment = $booking->payments->first();
        if (!$payment) {
            return response()->json(['success' => false, 'error' => 'Data payment tidak ditemukan'], 404);
        }

        $credentials = \App\Services\Payment\PaymentGatewayManager::getTenantCredentials($booking->tenant_id);
        if (!$credentials) {
            return response()->json(['success' => false, 'error' => 'Gateway pembayaran online belum diaktifkan oleh pengelola venue.'], 400);
        }

        $adapter = \App\Services\Payment\PaymentGatewayManager::getAdapter($credentials['provider']);
        $res = $adapter->createTransaction([
            'order_id'       => $payment->payment_number,
            'amount'         => (float) $payment->amount,
            'item_id'        => $booking->booking_code,
            'item_name'      => 'Booking Lapangan ' . ($booking->resource->name ?? 'Venue'),
            'customer_name'  => $booking->customer->name ?? 'Pelanggan',
            'customer_email' => $booking->customer->email ?? 'customer@example.com',
            'customer_phone' => $booking->customer->phone ?? '081234567890',
        ], $credentials);

        if (!$res['success']) {
            return response()->json(['success' => false, 'error' => $res['error'] ?? 'Gagal membuat tagihan.'], 400);
        }

        return response()->json([
            'success'      => true,
            'snap_token'   => $res['snap_token'] ?? null,
            'redirect_url' => $res['redirect_url'] ?? null,
        ]);
    }

    /**
     * Upload Bukti Transfer oleh Pelanggan
     */
    public function uploadProof(string $bookingCode, Request $request)
    {
        $booking = Booking::findByBookingCode($bookingCode);
        if (!$booking) {
            abort(404, 'Kode booking tidak ditemukan.');
        }

        $request->validate([
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:3072',
        ]);

        $payment = $booking->payments->first();
        if (!$payment) {
            return back()->with('error', 'Data pembayaran tidak ditemukan.');
        }

        $proofPath = minio_upload($request->file('payment_proof'), 'payment_proofs');
        $payment->update([
            'payment_proof' => $proofPath,
        ]);

        return back()->with('success', 'Bukti pembayaran berhasil diunggah! Petugas kami akan segera memverifikasi pesanan Anda.');
    }
}
