<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentService
{
    /**
     * Calculate the total amount due for a booking.
     */
    public function calculateAmount(Booking $booking): int
    {
        $nights      = $booking->check_in_date->diffInDays($booking->check_out_date);
        $nightlyRate = \App\Models\KennelSettings::sole()->nightly_rate_pence;

        return $nights * $nightlyRate;
    }

    /**
     * Create a Stripe PaymentIntent and return the client secret.
     * Returns null if Stripe credentials are not configured.
     */
    public function createPaymentIntent(Booking $booking): ?string
    {
        $secret = config('services.stripe.secret');

        if (! $secret) {
            return null;
        }

        Stripe::setApiKey($secret);

        $intent = PaymentIntent::create([
            'amount'                    => $booking->amount_pence,
            'currency'                  => 'gbp',
            'metadata'                  => ['booking_id' => $booking->id],
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        return $intent->client_secret;
    }

    /**
     * Record a confirmed Stripe payment against a booking and mark it paid.
     *
     * @param  array<string, mixed>  $stripePayload
     */
    public function recordPayment(Booking $booking, array $stripePayload): Payment
    {
        $payment = Payment::create([
            'booking_id'               => $booking->id,
            'stripe_payment_id'        => $stripePayload['id'],
            'stripe_payment_intent_id' => $stripePayload['id'],
            'amount_pence'             => $stripePayload['amount'],
            'currency'                 => $stripePayload['currency'] ?? 'gbp',
            'status'                   => $stripePayload['status'],
            'paid_at'                  => now(),
        ]);

        $booking->update(['payment_status' => 'paid']);

        return $payment;
    }
}
