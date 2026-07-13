<?php

namespace App\Policies;

use App\Enums\UserAccessLevel;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
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
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can view the model.
     *
     * Vendedores (Operational) podem visualizar pedidos, mas na tela
     * de detalhes devem ver APENAS o display_name e endereço do cliente
     * (dados estritamente necessários para envio e geração de etiqueta).
     * A restrição de campos é aplicada na camada de Controller/Resource.
     */
    public function view(User $user, Order $order): bool
    {
        return $user->isStaff();
    }

    /**
     * Vendedores (Operational) podem acessar detalhes do pedido
     * para gerar etiquetas, mas com campos limitados do cliente.
     */
    public function viewClientDetails(User $user, Order $order): bool
    {
        // Admin e SELLER_1 podem ver todos os dados do cliente
        if (in_array($user->access_level, [UserAccessLevel::ADMIN, UserAccessLevel::SELLER_1], true)) {
            return true;
        }

        // Operational (vendedor/loja) NÃO pode ver dados completos do cliente
        // Apenas display_name e endereço de entrega (aplicado no Resource/Controller)
        return false;
    }

    /**
     * Apenas Admin e Management podem acessar dados completos dos clientes.
     * Vendedores (Operational) têm acesso restrito — apenas nome e endereço para envio.
     */
    public function viewClientFullData(User $user): bool
    {
        return in_array($user->access_level, [UserAccessLevel::ADMIN, UserAccessLevel::SELLER_1], true);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Order $order): bool
    {
        return $user->isStaff();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Order $order): bool
    {
        return $user->isAdmin();
    }
}