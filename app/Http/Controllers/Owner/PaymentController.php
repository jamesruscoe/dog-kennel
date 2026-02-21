<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService) {}

    /**
     * Create a Stripe PaymentIntent for the given booking and return the client secret.
     */
    public function createIntent(Request $request, Booking $booking): JsonResponse
    {
        // Ensure the booking belongs to the authenticated owner
        $ownerId = $request->user()->owner?->id;
        abort_unless($booking->dog->owner_id === $ownerId, 403);

        // Only approved bookings can be paid
        $status = $booking->status instanceof \App\Enums\BookingStatus
            ? $booking->status->value
            : $booking->status;

        abort_unless($status === 'approved', 422, 'Only approved bookings can be paid.');

        // Prevent duplicate payments
        if ($booking->payment?->status === 'succeeded') {
            return response()->json(['error' => 'This booking has already been paid.'], 422);
        }

        $clientSecret = $this->paymentService->createPaymentIntent($booking);

        if (! $clientSecret) {
            return response()->json(['error' => 'Online payments are not currently available.'], 503);
        }

        return response()->json([
            'client_secret'  => $clientSecret,
            'amount_display' => $booking->amount_display,
        ]);
    }
}
