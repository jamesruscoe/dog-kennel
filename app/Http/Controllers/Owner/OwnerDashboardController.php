<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Http\Resources\CareLogResource;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\DogResource;
use App\Models\CareLog;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OwnerDashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $owner = $user->owner;

        $dogIds = $owner?->dogs()->pluck('dogs.id') ?? collect();

        // Unread conversations (max 3)
        $unreadConversations = Conversation::query()
            ->where('owner_user_id', $user->id)
            ->whereHas('messages', fn ($q) => $q->where('sender_id', '!=', $user->id)->whereNull('read_at'))
            ->with(['staffUser', 'ownerUser', 'latestMessage.sender'])
            ->orderByDesc('last_message_at')
            ->take(3)
            ->get();

        // Recent updates (last 5 care logs for owner's dogs)
        $recentUpdates = CareLog::query()
            ->with(['media', 'booking.dog', 'loggedByUser'])
            ->whereHas('booking', fn ($q) => $q->whereIn('dog_id', $dogIds))
            ->latest('occurred_at')
            ->take(5)
            ->get();

        return Inertia::render('Owner/Dashboard', [
            'dogs'            => DogResource::collection(
                $owner?->dogs()->withCount('bookings')->latest()->get() ?? collect()
            ),
            'activeBookings'  => BookingResource::collection(
                $owner?->activeBookings()->with('dog')->latest()->get() ?? collect()
            ),
            'unreadMessages'  => ConversationResource::collection($unreadConversations),
            'recentUpdates'   => CareLogResource::collection($recentUpdates),
        ]);
    }
}
