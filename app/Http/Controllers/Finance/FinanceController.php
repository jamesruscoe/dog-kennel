<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CompanyContext;
use App\Models\Payment;
use App\Services\StripeConnectionService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class FinanceController extends Controller
{
    public function __construct(private readonly StripeConnectionService $stripeService) {}

    public function index(): Response
    {
        $company = app(CompanyContext::class);

        $this->stripeService->refreshOnboardingStatus($company);

        $totalRevenuePence = Payment::where('status', 'succeeded')->sum('amount_pence');
        $succeededPayments = Payment::where('status', 'succeeded')->count();
        $pendingPayments   = Payment::where('status', 'pending')->count();
        $totalBookings     = Booking::count();
        $paidBookings      = Booking::whereHas('payment', fn ($q) => $q->where('status', 'succeeded'))->count();

        // Forecast: revenue from approved bookings not yet paid
        $forecastPence = Booking::where('status', 'approved')
            ->where(function ($q) {
                $q->whereNull('payment_status')->orWhere('payment_status', '!=', 'paid');
            })
            ->sum('amount_pence');

        // Outstanding: total from all approved + pending bookings
        $outstandingPence = Booking::whereIn('status', ['approved', 'pending'])
            ->sum('amount_pence');

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
                'forecast_pence'        => $forecastPence,
                'forecast_display'      => '£' . number_format($forecastPence / 100, 2),
                'outstanding_pence'     => $outstandingPence,
                'outstanding_display'   => '£' . number_format($outstandingPence / 100, 2),
            ],
            'recentPayments' => $recentPayments,
            'stripe' => [
                'connected'           => $this->stripeService->isConnected($company),
                'ready'               => $this->stripeService->isReady($company),
                'account_id'          => $company->stripe_account_id,
                'onboarding_complete' => $company->stripe_onboarding_complete,
            ],
        ]);
    }

    public function connectStripe(): JsonResponse
    {
        $company = app(CompanyContext::class);
        $url = $this->stripeService->getOnboardingUrl($company);

        if (! $url) {
            return response()->json([
                'error' => 'Stripe is not configured. Please add your Stripe API keys to the environment.',
            ], 422);
        }

        return response()->json(['url' => $url]);
    }
}
