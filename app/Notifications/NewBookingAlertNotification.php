<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * In-app alert sent to all staff when a new booking request is created.
 */
class NewBookingAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Booking $booking) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $ownerName = $this->booking->dog?->owner?->name ?? 'Unknown owner';
        $dogName   = $this->booking->dog?->name ?? 'Unknown dog';

        return [
            'booking_id'  => $this->booking->id,
            'dog_name'    => $dogName,
            'owner_name'  => $ownerName,
            'check_in'    => $this->booking->check_in_date?->toDateString(),
            'check_out'   => $this->booking->check_out_date?->toDateString(),
            'message'     => "New booking request: {$dogName} ({$ownerName})",
        ];
    }
}
