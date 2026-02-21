<?php

namespace App\Listeners;

use App\Events\BookingApproved;
use App\Notifications\BookingApprovedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBookingApprovedNotification implements ShouldQueue
{
    public function handle(BookingApproved $event): void
    {
        $owner = $event->booking->dog?->owner?->user;
        $owner?->notify(new BookingApprovedNotification($event->booking));
    }
}
