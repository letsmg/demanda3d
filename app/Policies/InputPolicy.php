<?php

namespace App\Policies;

use App\Enums\UserAccessLevel;
use App\Models\Input;
use App\Models\User;

class InputPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->access_level, [UserAccessLevel::ADMIN, UserAccessLevel::PARTNER]);
    }

    public function view(User $user, Input $input): bool
    {
        return in_array($user->access_level, [UserAccessLevel::ADMIN, UserAccessLevel::PARTNER]);
    }

    public function create(User $user): bool
    {
        return in_array($user->access_level, [UserAccessLevel::ADMIN, UserAccessLevel::PARTNER]);
    }

    public function update(User $user, Input $input): bool
    {
        return in_array($user->access_level, [UserAccessLevel::ADMIN, UserAccessLevel::PARTNER]);
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