<?php

namespace App\Policies;

use App\Models\Dog;
use App\Models\User;

class DogPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Dog $dog): bool
    {
        if ($user->isStaffOrAdmin()) {
            return true;
        }

        return $user->owner?->id === $dog->owner_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Dog $dog): bool
    {
        if ($user->isStaffOrAdmin()) {
            return true;
        }

        return $user->owner?->id === $dog->owner_id;
    }

    public function delete(User $user, Dog $dog): bool
    {
        if ($user->isStaffOrAdmin()) {
            return true;
        }

        return $user->owner?->id === $dog->owner_id;
    }
}
