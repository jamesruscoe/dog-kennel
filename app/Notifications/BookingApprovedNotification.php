<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Booking $booking) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Booking Confirmed!')
            ->line("Great news! Your booking for {$this->booking->dog?->name} has been confirmed.")
            ->line("Check-in: {$this->booking->check_in_date?->toDateString()}")
            ->line("Check-out: {$this->booking->check_out_date?->toDateString()}")
            ->action('View Booking', url('/owner/bookings/' . $this->booking->id));
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'dog_name'   => $this->booking->dog?->name,
            'message'    => 'Your booking has been confirmed.',
        ];
    }
}
