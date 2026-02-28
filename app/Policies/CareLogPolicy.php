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
        return $user->isStaffOrAdmin();
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
