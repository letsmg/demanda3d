<?php

namespace App\Policies;

use App\Models\Thread;
use App\Models\User;

class ThreadPolicy
{
    /**
     * Administradores têm acesso total a todas as threads para moderação.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any threads.
     */
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can view a specific thread.
     */
    public function view(User $user, Thread $thread): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        // Clientes só visualizam suas próprias threads
        return $thread->client_id === $user->id;
    }

    /**
     * Determine whether the user can create threads.
     */
    public function create(User $user): bool
    {
        // Qualquer usuário (staff ou cliente) pode criar uma thread
        return true;
    }

    /**
     * Determine whether the user can update the thread status.
     */
    public function update(User $user, Thread $thread): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can delete the thread.
     */
    public function delete(User $user, Thread $thread): bool
    {
        return $user->isAdmin();
    }
}