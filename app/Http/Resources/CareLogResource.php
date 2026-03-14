<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CareLogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'booking_id'    => $this->booking_id,
            'booking'       => new BookingResource($this->whenLoaded('booking')),
            'logged_by'     => $this->logged_by,
            'logged_by_user' => $this->when(
                $this->relationLoaded('loggedByUser'),
                fn () => ['id' => $this->loggedByUser->id, 'name' => $this->loggedByUser->name]
            ),
            'activity_type'  => $this->activity_type,
            'activity_label' => $this->activity_label,
            'notes'          => $this->notes,
            'media'          => $this->whenLoaded('media', function () {
                return $this->getMedia('care-log-photos')->map(function ($media) {
                    try {
                        $url = $media->getTemporaryUrl(now()->addMinutes(60));
                    } catch (\Exception) {
                        $url = $media->getUrl();
                    }

                    return [
                        'id'         => $media->id,
                        'uuid'       => $media->uuid,
                        'name'       => $media->name,
                        'file_name'  => $media->file_name,
                        'mime_type'  => $media->mime_type,
                        'size'       => $media->size,
                        'order'      => $media->order_column,
                        'signed_url' => $url,
                    ];
                });
            }),
            'occurred_at'   => $this->occurred_at?->toDateTimeString(),
            'created_at'    => $this->created_at?->toDateTimeString(),
        ];
    }
}
