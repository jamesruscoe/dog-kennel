<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
<<<<<<< Updated upstream
=======
use App\Services\StripeConnectionService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
>>>>>>> Stashed changes
use Inertia\Inertia;
use Inertia\Response;

class FinanceController extends Controller
{
<<<<<<< Updated upstream
=======
    public function __construct(
        private readonly StripeConnectionService $stripeService,
        private readonly SubscriptionService $subscriptionService,
    ) {}

>>>>>>> Stashed changes
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
<<<<<<< Updated upstream
        ]);
    }
=======
            'stripe' => [
                'connected'           => $this->stripeService->isConnected($company),
                'ready'               => $this->stripeService->isReady($company),
                'account_id'          => $company->stripe_account_id,
                'onboarding_complete' => $company->stripe_onboarding_complete,
            ],
            'subscription' => [
                'status'           => $company->subscription_status?->value,
                'status_label'     => $company->subscription_status?->label(),
                'is_active'        => $company->isSubscriptionActive(),
                'ends_at'          => $company->subscription_ends_at?->toIso8601String(),
                'has_subscription' => ! empty($company->stripe_subscription_id),
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

    public function subscribe(): JsonResponse
    {
        $company    = app(CompanyContext::class);
        $financeUrl = url("/{$company->slug}/staff/finance");

        $url = $this->subscriptionService->createCheckoutSession($company, $financeUrl, $financeUrl);

        if (! $url) {
            return response()->json([
                'error' => 'Stripe subscription is not configured. Please set STRIPE_SUBSCRIPTION_PRICE_ID in the environment.',
            ], 422);
        }

        return response()->json(['url' => $url]);
    }

    public function manageBilling(): JsonResponse
    {
        $company    = app(CompanyContext::class);
        $financeUrl = url("/{$company->slug}/staff/finance");

        $url = $this->subscriptionService->getManageUrl($company, $financeUrl);

        if (! $url) {
            return response()->json([
                'error' => 'Unable to create billing portal session.',
            ], 422);
        }

        return response()->json(['url' => $url]);
    }
>>>>>>> Stashed changes
}
