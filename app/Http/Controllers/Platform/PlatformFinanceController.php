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

        // Calculate platform fees earned per company
        $companies = Company::query()
            ->whereNotNull('stripe_account_id')
            ->get()
            ->map(function ($company) {
                $companyRevenue = Payment::withoutGlobalScopes()
                    ->where('company_id', $company->id)
                    ->where('status', 'succeeded')
                    ->sum('amount_pence');

                $paymentCount = Payment::withoutGlobalScopes()
                    ->where('company_id', $company->id)
                    ->where('status', 'succeeded')
                    ->count();

                $feePercent = $company->application_fee_percent ?? 0;
                $feesEarned = (int) round($companyRevenue * ($feePercent / 100));

                return [
                    'id'                      => $company->id,
                    'name'                    => $company->name,
                    'slug'                    => $company->slug,
                    'stripe_account_id'       => $company->stripe_account_id,
                    'application_fee_percent' => $feePercent,
                    'total_revenue_pence'     => $companyRevenue,
                    'total_revenue_display'   => '£' . number_format($companyRevenue / 100, 2),
                    'fees_earned_pence'       => $feesEarned,
                    'fees_earned_display'     => '£' . number_format($feesEarned / 100, 2),
                    'payment_count'           => $paymentCount,
                ];
            });

        $totalFeesEarned = $companies->sum('fees_earned_pence');

        return Inertia::render('Platform/Finance', [
            'stats' => [
                'total_processed_pence'   => $totalProcessedPence,
                'total_processed_display' => '£' . number_format($totalProcessedPence / 100, 2),
                'total_payments'          => $totalPayments,
                'total_fees_pence'        => $totalFeesEarned,
                'total_fees_display'      => '£' . number_format($totalFeesEarned / 100, 2),
                'connected_accounts'      => $companies->count(),
            ],
            'companies' => $companies->values(),
        ]);
    }
}
