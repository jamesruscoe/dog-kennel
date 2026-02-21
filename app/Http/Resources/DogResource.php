<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'owner_id'              => $this->owner_id,
            'owner'                 => new OwnerResource($this->whenLoaded('owner')),
            'name'                  => $this->name,
            'breed'                 => $this->breed,
            'date_of_birth'         => $this->date_of_birth?->toDateString(),
            'age_years'             => $this->date_of_birth?->diffInYears(now()),
            'sex'                   => $this->sex,
            'neutered'              => $this->neutered,
            'weight_kg'             => $this->weight_kg,
            'colour'                => $this->colour,
            'microchip_number'      => $this->microchip_number,
            'vet_name'              => $this->vet_name,
            'vet_phone'             => $this->vet_phone,
            'vaccination_confirmed' => $this->vaccination_confirmed,
            'medical_notes'         => $this->medical_notes,
            'dietary_notes'         => $this->dietary_notes,
            'behavioural_notes'     => $this->behavioural_notes,
            'bookings_count'        => $this->whenCounted('bookings'),
            'created_at'            => $this->created_at?->toDateTimeString(),
        ];
    }
}
