<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "updating" event.
     *
     * Quando o e-mail do usuário é alterado:
     * 1. Invalida a verificação (email_verified_at = null)
     * 2. Registra em log
     */
    public function updating(User $user): void
    {
        if ($user->isDirty('email')) {
            $oldEmail = $user->getOriginal('email');
            $newEmail = $user->email;

            $user->email_verified_at = null;

            Log::info('E-mail do usuário alterado — verificação invalidada.', [
                'user_id'   => $user->id,
                'old_email' => $oldEmail,
                'new_email' => $newEmail,
            ]);
        }
    }
}