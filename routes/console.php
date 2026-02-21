<?php

use App\Enums\BookingStatus;
use App\Jobs\SendDailySummaryEmail;
use App\Models\Owner;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─────────────────────────────────────────────────────────────────────────────
// Daily care-summary email — dispatched at 20:00 each evening
// Finds every owner who has at least one dog currently in stay and queues a
// SendDailySummaryEmail job so the notification is built and sent per-owner.
// ─────────────────────────────────────────────────────────────────────────────
Schedule::call(function () {
    Owner::whereHas('dogs', fn ($q) =>
        $q->whereHas('bookings', fn ($bq) =>
            $bq->whereIn('status', [BookingStatus::Approved->value, BookingStatus::Pending->value])
               ->whereDate('check_in_date', '<=', today())
               ->whereDate('check_out_date', '>', today())
        )
    )->each(fn (Owner $owner) => SendDailySummaryEmail::dispatch($owner));
})
->dailyAt('20:00')
->name('daily-summary-emails')
->withoutOverlapping();
