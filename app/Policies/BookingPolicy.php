<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Booking $booking): bool
    {
        if ($user->isStaffOrAdmin()) {
            return true;
        }

        return $user->owner?->id === $booking->dog?->owner_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Booking $booking): bool
    {
        return $user->isStaffOrAdmin();
    }

    public function cancel(User $user, Booking $booking): bool
    {
        if ($user->isStaffOrAdmin()) {
            return true;
        }

        return $user->owner?->id === $booking->dog?->owner_id;
    }

    public function delete(User $user, Booking $booking): bool
    {
        return $user->isStaffOrAdmin();
    }
}
