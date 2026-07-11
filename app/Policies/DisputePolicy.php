<?php

namespace App\Policies;

use App\Models\Dispute;
use App\Models\User;

class DisputePolicy
{
    /**
     * Administradores têm acesso total a todas as disputas para moderação.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any disputes.
     */
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can view a specific dispute.
     */
    public function view(User $user, Dispute $dispute): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        // Clientes só visualizam disputas que eles mesmos abriram
        return $dispute->reporter_id === $user->id;
    }

    /**
     * Determine whether the user can create disputes.
     */
    public function create(User $user): bool
    {
        return true; // Qualquer usuário autenticado pode abrir uma disputa
    }

    /**
     * Determine whether the user can update the dispute status.
     */
    public function update(User $user, Dispute $dispute): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can delete the dispute.
     */
    public function delete(User $user, Dispute $dispute): bool
    {
        return $user->isAdmin();
    }
}