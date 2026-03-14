<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CareLogMediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'signed_url' => $this->signed_url,
            'mime_type'  => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'order'      => $this->order,
        ];
    }
}
