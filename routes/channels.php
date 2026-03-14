<?php

use App\Models\Conversation;
use App\Models\CompanyContext;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('conversation.{conversationId}', function ($user, int $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (! $conversation) {
        return false;
    }

    // Verify conversation belongs to current company
    if (app()->bound(CompanyContext::class) && $conversation->company_id !== app(CompanyContext::class)->id) {
        return false;
    }

    return $user->id === $conversation->staff_user_id
        || $user->id === $conversation->owner_user_id;
});
