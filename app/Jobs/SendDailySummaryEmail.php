<?php

namespace App\Jobs;

use App\Models\Owner;
use App\Notifications\DailySummaryNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDailySummaryEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public readonly Owner $owner) {}

    public function handle(): void
    {
        $user = $this->owner->user;

        $activeBookings = $this->owner->activeBookings()
            ->with([
                'dog',
                'careLogs' => fn ($q) => $q
                    ->whereDate('occurred_at', today())
                    ->with('loggedByUser')
                    ->orderBy('occurred_at'),
            ])
            ->get();

        if ($activeBookings->isEmpty()) {
            return;
        }

        $user->notify(new DailySummaryNotification($activeBookings));
    }
}
