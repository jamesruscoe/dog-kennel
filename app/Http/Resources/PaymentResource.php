<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'booking_id'           => $this->booking_id,
            'stripe_payment_id'    => $this->stripe_payment_id,
            'amount_pence'         => $this->amount_pence,
            'amount_display'       => '£' . number_format($this->amount_pence / 100, 2),
            'currency'             => $this->currency,
            'status'               => $this->status,
            'paid_at'              => $this->paid_at?->toDateTimeString(),
        ];
    }
}
