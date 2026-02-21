<?php

namespace App\Notifications;

use App\Models\CareLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

/**
 * In-app notification sent to the booking owner when a care log entry is added.
 */
class CareLogAddedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly CareLog $careLog) {}

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
        $dogName       = $this->careLog->booking?->dog?->name ?? 'your dog';
        $activityLabel = $this->careLog->activity_label;

        return [
            'booking_id'     => $this->careLog->booking_id,
            'care_log_id'    => $this->careLog->id,
            'dog_name'       => $dogName,
            'activity_type'  => $this->careLog->activity_type,
            'activity_label' => $activityLabel,
            'message'        => "{$activityLabel} activity logged for {$dogName}.",
        ];
    }
}
