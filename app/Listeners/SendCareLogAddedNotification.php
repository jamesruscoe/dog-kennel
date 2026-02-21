<?php

namespace App\Listeners;

use App\Events\CareLogAdded;
use App\Notifications\CareLogAddedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendCareLogAddedNotification implements ShouldQueue
{
    public function handle(CareLogAdded $event): void
    {
        $owner = $event->careLog->booking?->dog?->owner?->user;
        $owner?->notify(new CareLogAddedNotification($event->careLog));
    }
}
