<?php

namespace App\Policies;

use App\Enums\UserAccessLevel;
use App\Models\Dispute;
use App\Models\User;

class DisputePolicy
{
    /**
     * Admin tem acesso total.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Vendedores podem ver disputas do seu próprio tenant.
     */
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Vendedores podem ver uma disputa específica do seu tenant.
     */
    public function view(User $user, Dispute $dispute): bool
    {
        // O TenantScope já garante o isolamento por tenant_id
        return $user->isStaff();
    }

    /**
     * Apenas clientes (via guard) podem criar disputas,
     * ou admins podem criar em nome de clientes.
     */
    public function create(User $user): bool
    {
        // Via painel staff, admins podem abrir disputas também
        return $user->isAdmin();
    }

    /**
     * Apenas admins podem atualizar/fechar disputas.
     * SELLER_1 e SELLER_2 NÃO podem intervir em disputas.
     */
    public function update(User $user, Dispute $dispute): bool
    {
        return $user->isAdmin();
    }

    /**
     * Apenas admins podem enviar mensagens em disputas.
     */
    public function sendMessage(User $user, Dispute $dispute): bool
    {
        return $user->isAdmin();
    }

    /**
     * Apenas admins podem fechar/resolver disputas.
     */
    public function close(User $user, Dispute $dispute): bool
    {
        return $user->isAdmin();
    }
}