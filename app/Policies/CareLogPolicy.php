<?php

namespace App\Policies;

use App\Models\CareLog;
use App\Models\User;

class CareLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaffOrAdmin();
    }

    public function view(User $user, CareLog $careLog): bool
    {
        if ($user->isStaffOrAdmin()) {
            return true;
        }

        if ($user->owner) {
            $careLog->loadMissing('booking');

            return $careLog->booking
                && $user->owner->dogs()->where('dogs.id', $careLog->booking->dog_id)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isStaffOrAdmin();
    }

    public function update(User $user, CareLog $careLog): bool
    {
        return $user->isStaffOrAdmin();
    }

    public function delete(User $user, CareLog $careLog): bool
    {
        return $user->isStaffOrAdmin();
    }
}
