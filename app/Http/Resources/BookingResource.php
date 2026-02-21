<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'dog'                  => new DogResource($this->whenLoaded('dog')),
            'check_in_date'        => $this->check_in_date?->toDateString(),
            'check_out_date'       => $this->check_out_date?->toDateString(),
            'nights'               => $this->check_in_date?->diffInDays($this->check_out_date),
            'status'               => $this->status instanceof \App\Enums\BookingStatus ? $this->status->value : $this->status,
            'status_label'         => $this->status_label,
            'notes'                => $this->notes,
            'special_requirements' => $this->special_requirements,
            'rejection_reason'     => $this->rejection_reason,
            'cancellation_reason'  => $this->cancellation_reason,
            'amount_pence'         => $this->amount_pence,
            'amount_display'       => $this->amount_display,
            'payment_status'       => $this->payment_status,
            'care_logs_count'      => $this->whenCounted('careLogs'),
            'care_logs'            => CareLogResource::collection($this->whenLoaded('careLogs')),
            'payment'              => new PaymentResource($this->whenLoaded('payment')),
            'created_at'           => $this->created_at?->toDateTimeString(),
            'updated_at'           => $this->updated_at?->toDateTimeString(),
        ];
    }
}
