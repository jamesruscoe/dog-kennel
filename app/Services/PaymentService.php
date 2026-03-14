<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Company;
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
        $nights      = max(1, $booking->check_in_date->diffInDays($booking->check_out_date));
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

        // Ensure the booking has a calculated amount
        if (! $booking->amount_pence) {
            $amount = $this->calculateAmount($booking);
            $booking->update(['amount_pence' => $amount]);
        }

        Stripe::setApiKey($secret);

        $company = $booking->company ?? Company::find($booking->company_id);
        $params = [
            'amount'                    => $booking->amount_pence,
            'currency'                  => 'gbp',
            'metadata'                  => ['booking_id' => $booking->id],
            'automatic_payment_methods' => ['enabled' => true],
        ];

        // Use Stripe Connect if the company has a connected account
        if ($company?->stripe_account_id) {
            $feePercent = $company->application_fee_percent ?? 0;
            $feeAmount  = (int) round($booking->amount_pence * ($feePercent / 100));

            $params['application_fee_amount'] = $feeAmount;
            $params['transfer_data'] = [
                'destination' => $company->stripe_account_id,
            ];
        }

        $intent = PaymentIntent::create($params);

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
            'company_id'               => $booking->company_id,
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
