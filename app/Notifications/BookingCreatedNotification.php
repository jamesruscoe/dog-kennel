<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCreatedNotification extends Notification implements ShouldQueue
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
        $dogName  = $this->booking->dog?->name ?? 'your dog';
        $checkIn  = $this->booking->check_in_date?->toDateString();
        $checkOut = $this->booking->check_out_date?->toDateString();
        $nights   = $this->booking->nights ?? 0;

        return (new MailMessage)
            ->subject('Booking Request Received')
            ->greeting("Hi {$notifiable->name}!")
            ->line("We've received your booking request for **{$dogName}**.")
            ->line("**Check-in:** {$checkIn}")
            ->line("**Check-out:** {$checkOut} ({$nights} night" . ($nights === 1 ? '' : 's') . ')')
            ->line('We will review your request and confirm shortly.')
            ->action('View Booking', url('/owner/bookings/' . $this->booking->id))
            ->line('Thank you for choosing our kennel!');
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'booking_id' => $this->booking->id,
            'dog_name'   => $this->booking->dog?->name,
            'message'    => 'Your booking request has been received and is pending approval.',
        ];
    }
}
