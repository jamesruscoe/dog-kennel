<?php

namespace App\Http\Controllers\Staff;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Dog;
use App\Models\Owner;
use App\Models\Payment;
use App\Services\CapacityService;
use Inertia\Inertia;
use Inertia\Response;

class StaffDashboardController extends Controller
{
    public function __construct(private readonly CapacityService $capacityService) {}

    public function __invoke(): Response
    {
        $today       = today();
        $maxCapacity = $this->capacityService->maxCapacity();

        // ── Today's movements ─────────────────────────────────────────────────
        $checkInsToday = Booking::with(['dog.owner.user'])
            ->whereDate('check_in_date', $today)
            ->whereIn('status', [BookingStatus::Approved->value, BookingStatus::Pending->value])
            ->orderBy('check_in_date')
            ->get();

        $checkOutsToday = Booking::with(['dog.owner.user'])
            ->whereDate('check_out_date', $today)
            ->where('status', BookingStatus::Approved->value)
            ->orderBy('check_out_date')
            ->get();

        // ── Pending bookings needing approval ─────────────────────────────────
        $pendingBookings = Booking::with(['dog.owner.user'])
            ->where('status', BookingStatus::Pending->value)
            ->latest()
            ->take(6)
            ->get();

        // ── Revenue this calendar month ───────────────────────────────────────
        $revenueMonth = Payment::where('status', 'succeeded')
            ->whereMonth('paid_at', $today->month)
            ->whereYear('paid_at', $today->year)
            ->sum('amount_pence');

        // ── 14-day occupancy for chart ────────────────────────────────────────
        $occupancy14 = $this->capacityService->occupancyByDate(
            $today,
            $today->copy()->addDays(13),
        );

        return Inertia::render('Staff/Dashboard', [
            'metrics' => [
                'total_owners'     => Owner::count(),
                'total_dogs'       => Dog::count(),
                'active_bookings'  => Booking::whereIn('status', [
                    BookingStatus::Approved->value, BookingStatus::Pending->value,
                ])->count(),
                'pending_approval' => Booking::where('status', BookingStatus::Pending->value)->count(),
                'in_stay_today'    => Booking::where('status', BookingStatus::Approved->value)
                    ->whereDate('check_in_date', '<=', $today)
                    ->whereDate('check_out_date', '>', $today)
                    ->count(),
                'todays_checkins'  => $checkInsToday->count(),
                'todays_checkouts' => $checkOutsToday->count(),
                'revenue_display'  => '£' . number_format($revenueMonth / 100, 2),
            ],
            'maxCapacity'    => $maxCapacity,
            'occupancy14'    => $occupancy14,
            'checkInsToday'  => BookingResource::collection($checkInsToday),
            'checkOutsToday' => BookingResource::collection($checkOutsToday),
            'pendingBookings' => BookingResource::collection($pendingBookings),
        ]);
    }
}
