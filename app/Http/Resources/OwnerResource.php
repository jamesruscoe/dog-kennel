<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OwnerResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'user_id'                 => $this->user_id,
            'name'                    => $this->user->name,
            'email'                   => $this->user->email,
            'phone'                   => $this->phone,
            'address'                 => $this->address,
            'emergency_contact_name'  => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'notes'                   => $this->notes,
            'dogs_count'              => $this->whenCounted('dogs'),
            'dogs'                    => DogResource::collection($this->whenLoaded('dogs')),
            'created_at'              => $this->created_at?->toDateTimeString(),
            'updated_at'              => $this->updated_at?->toDateTimeString(),
        ];
    }
}
