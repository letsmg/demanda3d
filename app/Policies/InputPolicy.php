<?php

namespace App\Policies;

use App\Models\Input;
use App\Models\User;

class InputPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Input $input): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Input $input): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Input $input): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Input $input): bool
    {
        return $user->isAdmin();
    }
}