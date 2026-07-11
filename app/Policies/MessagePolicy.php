<?php

namespace App\Policies;

use App\Models\Message;
use App\Models\User;

class MessagePolicy
{
    /**
     * Administradores têm acesso total a todas as mensagens para moderação.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null; // continua para os métodos específicos
    }

    /**
     * Determine whether the user can view any messages.
     */
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can view a specific message.
     */
    public function view(User $user, Message $message): bool
    {
        if ($user->isStaff()) {
            return true;
        }

        // Clientes só visualizam mensagens de suas próprias threads
        return $message->thread?->client_id === $user->id;
    }

    /**
     * Determine whether the user can create messages.
     */
    public function create(User $user): bool
    {
        return true; // Staff e clientes podem criar mensagens
    }

    /**
     * Determine whether the user can update the message (não implementado).
     */
    public function update(User $user, Message $message): bool
    {
        return false; // Mensagens são imutáveis
    }

    /**
     * Determine whether the user can delete the message.
     */
    public function delete(User $user, Message $message): bool
    {
        return $user->isAdmin(); // Apenas admin pode deletar
    }
}