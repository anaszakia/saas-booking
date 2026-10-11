<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Venue;
use App\Traits\HasAutoPermissions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;

class BookingCalendarController extends Controller implements HasMiddleware
{
    use HasAutoPermissions;

    public static function resolveResourceSlugStatic(): string
    {
        return 'bookings';
    }

    public function index(Request $request)
    {
        $tenantId = current_tenant_id() ?? 1;
        $venues   = Venue::getDropdownList($tenantId);

        // Default venue pertama jika tidak dipilih
        $venueId = $request->query('venue_id') ? (int) $request->query('venue_id') : array_key_first($venues);
        $date    = $request->query('date', Carbon::today()->toDateString());

        $matrix = [
            'resources' => collect(),
            'bookings'  => collect(),
            'date'      => $date,
        ];

        if ($venueId) {
            $matrix = Booking::getCalendarMatrix($tenantId, $venueId, $date);
        }

        // Generate jam operasional grid (08:00 sampai 23:00)
        $timeSlots = [];
        for ($hour = 8; $hour <= 22; $hour++) {
            $timeSlots[] = sprintf('%02d:00', $hour);
        }

        return view('admin.bookings.calendar', compact('venues', 'venueId', 'date', 'matrix', 'timeSlots'));
    }
}
