<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Payment;
use Inertia\Inertia;
use Inertia\Response;

class PlatformFinanceController extends Controller
{
    public function index(): Response
    {
        // Total payments across all tenants (bypass global scopes)
        $totalProcessedPence = Payment::withoutGlobalScopes()
            ->where('status', 'succeeded')
            ->sum('amount_pence');

        $totalPayments = Payment::withoutGlobalScopes()
            ->where('status', 'succeeded')
            ->count();

        $companies = Company::all()
            ->map(function ($company) {
                $companyRevenue = Payment::withoutGlobalScopes()
                    ->where('company_id', $company->id)
                    ->where('status', 'succeeded')
                    ->sum('amount_pence');

                $paymentCount = Payment::withoutGlobalScopes()
                    ->where('company_id', $company->id)
                    ->where('status', 'succeeded')
                    ->count();

                return [
                    'id'                     => $company->id,
                    'name'                   => $company->name,
                    'slug'                   => $company->slug,
                    'stripe_account_id'      => $company->stripe_account_id,
                    'subscription_status'    => $company->subscription_status?->value,
                    'subscription_label'     => $company->subscription_status?->label(),
                    'subscription_active'    => $company->isSubscriptionActive(),
                    'total_revenue_pence'    => $companyRevenue,
                    'total_revenue_display'  => '£' . number_format($companyRevenue / 100, 2),
                    'payment_count'          => $paymentCount,
                ];
            });

        $activeSubscriptions = $companies->where('subscription_active', true)->count();

        return Inertia::render('Platform/Finance', [
            'stats' => [
                'total_processed_pence'   => $totalProcessedPence,
                'total_processed_display' => '£' . number_format($totalProcessedPence / 100, 2),
                'total_payments'          => $totalPayments,
                'active_subscriptions'    => $activeSubscriptions,
                'total_companies'         => $companies->count(),
            ],
            'companies' => $companies->values(),
        ]);
    }
}
