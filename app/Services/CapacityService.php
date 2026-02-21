<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\KennelSettings;
use App\Enums\BookingStatus;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class CapacityService
{
    /**
     * Return the kennel's configured maximum capacity.
     */
    public function maxCapacity(): int
    {
        // TODO: JOB 6 — load from KennelSettings
        return KennelSettings::sole()->max_capacity;
    }

    /**
     * Return a keyed collection of occupied spots for each date in the given range.
     * Keys are date strings (Y-m-d), values are occupancy counts.
     *
     * @return Collection<string, int>
     */
    public function occupancyByDate(Carbon $from, Carbon $to): Collection
    {
        // TODO: JOB 6 — implement with efficient single query
        $dates = collect();

        foreach (CarbonPeriod::create($from, $to) as $date) {
            $key = $date->toDateString();
            $dates[$key] = Booking::query()
                ->whereIn('status', [BookingStatus::Approved->value, BookingStatus::Pending->value])
                ->where('check_in_date', '<=', $key)
                ->where('check_out_date', '>', $key)
                ->count();
        }

        return $dates;
    }

    /**
     * Check whether capacity is available for every night of a proposed booking window.
     */
    public function isAvailable(Carbon $checkIn, Carbon $checkOut, ?int $excludeBookingId = null): bool
    {
        // TODO: JOB 6 — implement with exclude for update scenarios
        $max = $this->maxCapacity();

        foreach (CarbonPeriod::create($checkIn, $checkOut->subDay()) as $date) {
            $key = $date->toDateString();
            $count = Booking::query()
                ->whereIn('status', [BookingStatus::Approved->value, BookingStatus::Pending->value])
                ->where('check_in_date', '<=', $key)
                ->where('check_out_date', '>', $key)
                ->when($excludeBookingId, fn($q) => $q->where('id', '!=', $excludeBookingId))
                ->count();

            if ($count >= $max) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return the first available date on or after the given date.
     */
    public function nextAvailableDate(Carbon $from): ?Carbon
    {
        // TODO: JOB 6 — implement
        return null;
    }
}
