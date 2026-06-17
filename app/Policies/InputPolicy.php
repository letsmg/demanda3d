<?php

namespace App\Policies;

use App\Enums\UserAccessLevel;
use App\Models\Input;
use App\Models\User;

class InputPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->access_level, [UserAccessLevel::ADMIN, UserAccessLevel::STAFF]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Input $input): bool
    {
        return in_array($user->access_level, [UserAccessLevel::ADMIN, UserAccessLevel::STAFF]);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->access_level, [UserAccessLevel::ADMIN, UserAccessLevel::STAFF]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Input $input): bool
    {
        return in_array($user->access_level, [UserAccessLevel::ADMIN, UserAccessLevel::STAFF]);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Input $input): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Input $input): bool
    {
        return $user->isAdmin();
    }
}