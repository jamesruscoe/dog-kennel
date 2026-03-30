<?php

namespace App\Services;

use App\Enums\SubscriptionStatus;
use App\Models\Company;
use Stripe\StripeClient;

class SubscriptionService
{
    private function stripe(): ?StripeClient
    {
        $secret = config('services.stripe.secret');

        return $secret ? new StripeClient($secret) : null;
    }

    /**
     * Create a Stripe Checkout Session for the monthly subscription.
     * Returns the Checkout URL, or null if Stripe is not configured.
     */
    public function createCheckoutSession(Company $company, string $successUrl, string $cancelUrl): ?string
    {
        $stripe = $this->stripe();

        if (! $stripe || ! config('services.stripe.subscription_price_id')) {
            return null;
        }

        // Create a Stripe Customer for the company if one doesn't exist
        if (! $company->stripe_customer_id) {
            $customer = $stripe->customers->create([
                'name'     => $company->name,
                'metadata' => ['company_id' => $company->id, 'company_slug' => $company->slug],
            ]);
            $company->update(['stripe_customer_id' => $customer->id]);
        }

        $session = $stripe->checkout->sessions->create([
            'customer'             => $company->stripe_customer_id,
            'mode'                 => 'subscription',
            'payment_method_types' => ['card', 'bacs_debit'],
            'line_items'           => [[
                'price'    => config('services.stripe.subscription_price_id'),
                'quantity' => 1,
            ]],
            'success_url' => $successUrl,
            'cancel_url'  => $cancelUrl,
        ]);

        return $session->url;
    }

    /**
     * Sync subscription status from Stripe to the local database.
     */
    public function syncSubscriptionStatus(Company $company): void
    {
        $stripe = $this->stripe();

        if (! $stripe || ! $company->stripe_subscription_id) {
            return;
        }

        try {
            $subscription = $stripe->subscriptions->retrieve($company->stripe_subscription_id);

            $company->update([
                'subscription_status'  => $subscription->status,
                'subscription_ends_at' => \Carbon\Carbon::createFromTimestamp($subscription->current_period_end),
            ]);
        } catch (\Throwable) {
            // Silently fail — status will be checked again on next webhook or page load
        }
    }

    /**
     * Cancel the subscription at period end.
     */
    public function cancelSubscription(Company $company): void
    {
        $stripe = $this->stripe();

        if (! $stripe || ! $company->stripe_subscription_id) {
            return;
        }

        $stripe->subscriptions->update($company->stripe_subscription_id, [
            'cancel_at_period_end' => true,
        ]);

        $this->syncSubscriptionStatus($company);
    }

    /**
     * Generate a Stripe Billing Portal URL so the company can manage payment method, invoices, etc.
     */
    public function getManageUrl(Company $company, string $returnUrl): ?string
    {
        $stripe = $this->stripe();

        if (! $stripe || ! $company->stripe_customer_id) {
            return null;
        }

        try {
            $session = $stripe->billingPortal->sessions->create([
                'customer'   => $company->stripe_customer_id,
                'return_url' => $returnUrl,
            ]);

            return $session->url;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Handle a Stripe subscription object update (from webhook).
     * Finds the company by stripe_customer_id and updates local state.
     */
    public function handleSubscriptionUpdated(object $subscription): void
    {
        $company = Company::where('stripe_customer_id', $subscription->customer)->first();

        if (! $company) {
            return;
        }

        $company->update([
            'stripe_subscription_id' => $subscription->id,
            'subscription_status'    => $subscription->status,
            'subscription_ends_at'   => \Carbon\Carbon::createFromTimestamp($subscription->current_period_end),
        ]);
    }

    /**
     * Handle subscription deletion (from webhook).
     */
    public function handleSubscriptionDeleted(object $subscription): void
    {
        $company = Company::where('stripe_customer_id', $subscription->customer)->first();

        if (! $company) {
            return;
        }

        $company->update([
            'subscription_status'  => SubscriptionStatus::Canceled->value,
            'subscription_ends_at' => now(),
        ]);
    }

    /**
     * Handle invoice payment — sync subscription status.
     */
    public function handleInvoicePaid(object $invoice): void
    {
        if (! $invoice->subscription) {
            return;
        }

        $company = Company::where('stripe_customer_id', $invoice->customer)->first();

        if ($company) {
            $this->syncSubscriptionStatus($company);
        }
    }

    /**
     * Handle failed invoice — sync subscription status (will become past_due/unpaid).
     */
    public function handleInvoicePaymentFailed(object $invoice): void
    {
        if (! $invoice->subscription) {
            return;
        }

        $company = Company::where('stripe_customer_id', $invoice->customer)->first();

        if ($company) {
            $this->syncSubscriptionStatus($company);
        }
    }
}
