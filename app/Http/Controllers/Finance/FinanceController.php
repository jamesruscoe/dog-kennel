<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Inertia\Inertia;
use Inertia\Response;

class FinanceController extends Controller
{
    public function index(): Response
    {
        $totalRevenuePence = Payment::where('status', 'succeeded')->sum('amount_pence');
        $succeededPayments = Payment::where('status', 'succeeded')->count();
        $pendingPayments   = Payment::where('status', 'pending')->count();
        $totalBookings     = Booking::count();
        $paidBookings      = Booking::whereHas('payment', fn ($q) => $q->where('status', 'succeeded'))->count();

        $recentPayments = Payment::with(['booking.dog'])
            ->latest('paid_at')
            ->limit(20)
            ->get()
            ->map(fn ($payment) => [
                'id'             => $payment->id,
                'amount_pence'   => $payment->amount_pence,
                'amount_display' => $payment->amount_display,
                'status'         => $payment->status,
                'paid_at'        => $payment->paid_at?->toIso8601String(),
                'booking'        => $payment->booking ? [
                    'id'             => $payment->booking->id,
                    'check_in_date'  => $payment->booking->check_in_date?->toDateString(),
                    'check_out_date' => $payment->booking->check_out_date?->toDateString(),
                    'dog'            => $payment->booking->dog
                        ? ['name' => $payment->booking->dog->name]
                        : null,
                ] : null,
            ]);

        return Inertia::render('Staff/Finance/Index', [
            'stats' => [
                'total_revenue_pence'   => $totalRevenuePence,
                'total_revenue_display' => '£' . number_format($totalRevenuePence / 100, 2),
                'succeeded_payments'    => $succeededPayments,
                'pending_payments'      => $pendingPayments,
                'total_bookings'        => $totalBookings,
                'paid_bookings'         => $paidBookings,
            ],
            'recentPayments' => $recentPayments,
        ]);
    }
}
