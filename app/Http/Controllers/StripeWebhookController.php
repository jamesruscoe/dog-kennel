<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\PaymentService;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Event;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly SubscriptionService $subscriptionService,
    ) {}

    /**
     * Handle incoming Stripe webhook events.
     * Registered at POST /api/webhooks/stripe (no CSRF, no auth).
     */
    public function __invoke(Request $request): JsonResponse
    {
        $payload   = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        Stripe::setApiKey(config('services.stripe.secret'));

        // Verify webhook signature when a secret is configured
        if ($secret && $signature) {
            try {
                $event = Webhook::constructEvent($payload, $signature, $secret);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Invalid Stripe signature.'], 400);
            }
        } else {
            // No webhook secret — parse raw payload (development only)
            $event = Event::constructFrom(json_decode($payload, true));
        }

<<<<<<< Updated upstream
        if ($event->type === 'payment_intent.succeeded') {
            $intent    = $event->data->object;
            $bookingId = $intent->metadata->booking_id ?? null;

            if ($bookingId) {
                $booking = Booking::find($bookingId);

                // Only record if booking exists and has no payment yet
                if ($booking && ! $booking->payment) {
                    $this->paymentService->recordPayment($booking, [
                        'id'       => $intent->id,
                        'amount'   => $intent->amount_received,
                        'currency' => $intent->currency,
                        'status'   => 'succeeded',
                    ]);
                }
            }
        }

        return response()->json(['received' => true]);
    }
=======
        match ($event->type) {
            'payment_intent.succeeded'            => $this->handlePaymentIntentSucceeded($event),
            'account.updated'                     => $this->handleAccountUpdated($event),
            'customer.subscription.created',
            'customer.subscription.updated'       => $this->handleSubscriptionUpdated($event),
            'customer.subscription.deleted'       => $this->handleSubscriptionDeleted($event),
            'invoice.paid'                        => $this->handleInvoicePaid($event),
            'invoice.payment_failed'              => $this->handleInvoicePaymentFailed($event),
            default                               => null,
        };

        return response()->json(['received' => true]);
    }

    private function handlePaymentIntentSucceeded(Event $event): void
    {
        $intent    = $event->data->object;
        $bookingId = $intent->metadata->booking_id ?? null;

        if ($bookingId) {
            $booking = Booking::find($bookingId);

            if ($booking && ! $booking->payment) {
                $this->paymentService->recordPayment($booking, [
                    'id'       => $intent->id,
                    'amount'   => $intent->amount_received,
                    'currency' => $intent->currency,
                    'status'   => 'succeeded',
                ]);
            }
        }
    }

    private function handleAccountUpdated(Event $event): void
    {
        $account = $event->data->object;

        $company = Company::where('stripe_account_id', $account->id)->first();

        if (! $company) {
            return;
        }

        $isComplete = $account->charges_enabled && $account->details_submitted;

        if ($company->stripe_onboarding_complete !== $isComplete) {
            $company->update(['stripe_onboarding_complete' => $isComplete]);
        }
    }

    private function handleSubscriptionUpdated(Event $event): void
    {
        $this->subscriptionService->handleSubscriptionUpdated($event->data->object);
    }

    private function handleSubscriptionDeleted(Event $event): void
    {
        $this->subscriptionService->handleSubscriptionDeleted($event->data->object);
    }

    private function handleInvoicePaid(Event $event): void
    {
        $this->subscriptionService->handleInvoicePaid($event->data->object);
    }

    private function handleInvoicePaymentFailed(Event $event): void
    {
        $this->subscriptionService->handleInvoicePaymentFailed($event->data->object);
    }
>>>>>>> Stashed changes
}
