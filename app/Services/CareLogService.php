<?php

namespace App\Services;

use App\Events\CareLogAdded;
use App\Models\Booking;
use App\Models\CareLog;
use Illuminate\Pagination\LengthAwarePaginator;

class CareLogService
{
    /**
     * Return paginated care logs, optionally scoped to a booking.
     *
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters = [], ?Booking $booking = null): LengthAwarePaginator
    {
        return CareLog::query()
            ->with(['booking.dog.owner.user', 'loggedByUser'])
            ->when($booking, fn ($q) => $q->where('booking_id', $booking->id))
            ->when($filters['activity_type'] ?? null, fn ($q, $t) => $q->where('activity_type', $t))
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('occurred_at', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('occurred_at', '<=', $d))
            ->latest('occurred_at')
            ->paginate(30)
            ->withQueryString();
    }

    /**
     * Log a care activity for a booking.
     *
     * @param  array<string, mixed>  $data
     */
    public function log(Booking $booking, int $staffUserId, array $data): CareLog
    {
        // TODO: JOB 8 — implement with media attachments (photos)
        $entry = CareLog::create([
            'booking_id'      => $booking->id,
            'logged_by'       => $staffUserId,
            'activity_type'   => $data['activity_type'],
            'notes'           => $data['notes'] ?? null,
            'occurred_at'     => $data['occurred_at'] ?? now(),
        ]);

        event(new CareLogAdded($entry));

        return $entry->load('loggedByUser');
    }

    /**
     * Update an existing care log entry.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(CareLog $careLog, array $data): CareLog
    {
        // TODO: JOB 8 — implement
        $careLog->update($data);

        return $careLog->fresh();
    }

    /**
     * Delete a care log entry.
     */
    public function delete(CareLog $careLog): void
    {
        // TODO: JOB 8 — implement
        $careLog->delete();
    }
}
