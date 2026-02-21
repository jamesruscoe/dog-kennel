<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Event;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

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
}
