<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KennelSettingsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'max_capacity'         => $this->max_capacity,
            'nightly_rate_pence'   => $this->nightly_rate_pence,
            'nightly_rate_display' => '£' . number_format($this->nightly_rate_pence / 100, 2),
            'operating_days'       => $this->operating_days,
            'check_in_time'        => $this->check_in_time,
            'check_out_time'       => $this->check_out_time,
            'booking_lead_days'    => $this->booking_lead_days,
            'terms_and_conditions' => $this->terms_and_conditions,
            'updated_at'           => $this->updated_at?->toDateTimeString(),
        ];
    }
}
