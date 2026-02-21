<?php

namespace App\Listeners;

use App\Enums\UserRole;
use App\Events\BookingCreated;
use App\Models\User;
use App\Notifications\BookingCreatedNotification;
use App\Notifications\NewBookingAlertNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBookingCreatedNotification implements ShouldQueue
{
    public function handle(BookingCreated $event): void
    {
        // Email + in-app notification to the dog owner
        $owner = $event->booking->dog?->owner?->user;
        $owner?->notify(new BookingCreatedNotification($event->booking));

        // In-app alert to all staff members
        User::where('role', UserRole::Staff->value)
            ->each(fn (User $staff) => $staff->notify(new NewBookingAlertNotification($event->booking)));
    }
}
