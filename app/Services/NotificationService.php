<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Return unread database notifications for a user.
     *
     * @return Collection<int, \Illuminate\Notifications\DatabaseNotification>
     */
    public function unreadFor(User $user): Collection
    {
        return $user->unreadNotifications()->latest()->take(50)->get();
    }

    /**
     * Mark a single notification as read.
     */
    public function markRead(User $user, string $notificationId): void
    {
        $user->notifications()->where('id', $notificationId)->first()?->markAsRead();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public function markAllRead(User $user): void
    {
        $user->unreadNotifications()->update(['read_at' => now()]);
    }
}
