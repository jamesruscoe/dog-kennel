<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class DailySummaryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Collection<int, \App\Models\Booking>  $bookings
     */
    public function __construct(private readonly Collection $bookings) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $dateLabel = today()->format('j F Y');
        $dogCount  = $this->bookings->count();
        $dogWord   = $dogCount === 1 ? 'dog' : 'dogs';

        $mail = (new MailMessage)
            ->subject("Daily Care Update — {$dateLabel}")
            ->greeting("Hi {$notifiable->name}!")
            ->line("Here's today's care update for your {$dogWord} at the kennel ({$dateLabel}).");

        foreach ($this->bookings as $booking) {
            $dog     = $booking->dog;
            $dogName = $dog?->name ?? 'Your dog';
            $breed   = $dog?->breed ? " ({$dog->breed})" : '';
            $checkIn  = $booking->check_in_date?->format('j M');
            $checkOut = $booking->check_out_date?->format('j M Y');
            $logs    = $booking->careLogs;

            // ── Dog section header ──────────────────────────────────────────
            $mail->line('---');
            $mail->line("**{$dogName}**{$breed}  |  Stay: {$checkIn} – {$checkOut}");

            if ($logs->isEmpty()) {
                $mail->line('*No activities have been logged for today yet.*');
                continue;
            }

            $activityCount = $logs->count();
            $activityWord  = $activityCount === 1 ? 'activity' : 'activities';
            $mail->line("**{$activityCount} {$activityWord} logged today:**");

            foreach ($logs as $log) {
                $time  = $log->occurred_at?->format('H:i');
                $label = $log->activity_label;
                $staff = $log->loggedByUser?->name ?? 'Staff';
                $notes = $log->notes ? " — {$log->notes}" : '';

                $mail->line("- **{$label}** at {$time}{$notes} *(logged by {$staff})*");
            }
        }

        $mail->line('---');

        return $mail
            ->action('View Your Bookings', url('/owner/bookings'))
            ->line('Thank you for choosing our kennel!');
    }
}
