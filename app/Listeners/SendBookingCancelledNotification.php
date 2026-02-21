<?php

namespace App\Listeners;

use App\Events\BookingCancelled;
use App\Notifications\BookingCancelledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBookingCancelledNotification implements ShouldQueue
{
    public function handle(BookingCancelled $event): void
    {
        $owner = $event->booking->dog?->owner?->user;
        $owner?->notify(new BookingCancelledNotification($event->booking));
    }
}
