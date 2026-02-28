<?php

namespace App\Services;

use App\Models\Company;

class StripeConnectionService
{
    /**
     * Whether the company has a Stripe Connect account linked.
     */
    public function isConnected(Company $company): bool
    {
        return ! empty($company->stripe_account_id);
    }

    /**
     * Whether the company is fully Stripe-ready (connected + onboarding complete).
     * Bookings can only be created when this returns true.
     */
    public function isReady(Company $company): bool
    {
        return $this->isConnected($company) && $company->stripe_onboarding_complete;
    }

    /**
     * Generate a Stripe Connect onboarding URL for the given company.
     * Returns null if Stripe is not configured in the environment.
     */
    public function getOnboardingUrl(Company $company): ?string
    {
        if (empty(config('services.stripe.secret'))) {
            return null;
        }

        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));

            if (empty($company->stripe_account_id)) {
                $account = $stripe->accounts->create(['type' => 'express']);
                $company->update(['stripe_account_id' => $account->id]);
            }

            $link = $stripe->accountLinks->create([
                'account'     => $company->stripe_account_id,
                'refresh_url' => url("/{$company->slug}/staff/settings"),
                'return_url'  => url("/{$company->slug}/staff/settings"),
                'type'        => 'account_onboarding',
            ]);

            return $link->url;
        } catch (\Throwable) {
            return null;
        }
    }
}
