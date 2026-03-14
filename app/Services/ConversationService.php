<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessageNotification;

class ConversationService
{
    public function findOrCreate(int $companyId, int $staffUserId, int $ownerUserId): Conversation
    {
        return Conversation::firstOrCreate(
            [
                'company_id'    => $companyId,
                'staff_user_id' => $staffUserId,
                'owner_user_id' => $ownerUserId,
            ],
        );
    }

    public function sendMessage(Conversation $conversation, User $sender, string $body): Message
    {
        $message = Message::create([
            'company_id'      => $conversation->company_id,
            'conversation_id' => $conversation->id,
            'sender_id'       => $sender->id,
            'body'            => $body,
        ]);

        $conversation->update(['last_message_at' => now()]);

        event(new MessageSent($message));

        // Notify the other party
        $recipientId = $sender->id === $conversation->staff_user_id
            ? $conversation->owner_user_id
            : $conversation->staff_user_id;

        $recipient = User::find($recipientId);

        if ($recipient) {
            $recipient->notify(new NewMessageNotification($conversation, $sender, $body));
        }

        return $message->load('sender');
    }

    public function markAsRead(Conversation $conversation, User $reader): void
    {
        $conversation->messages()
            ->where('sender_id', '!=', $reader->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
