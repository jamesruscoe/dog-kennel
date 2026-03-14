<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $userId = $request->user()?->id;

        return [
            'id'              => $this->id,
            'staff_user'      => $this->whenLoaded('staffUser', fn () => [
                'id'   => $this->staffUser->id,
                'name' => $this->staffUser->name,
            ]),
            'owner_user'      => $this->whenLoaded('ownerUser', fn () => [
                'id'   => $this->ownerUser->id,
                'name' => $this->ownerUser->name,
            ]),
            'last_message_at' => $this->last_message_at?->toDateTimeString(),
            'unread_count'    => $userId ? $this->unreadCountFor($userId) : 0,
            'latest_message'  => $this->whenLoaded('latestMessage', fn () => new MessageResource($this->latestMessage)),
        ];
    }
}
