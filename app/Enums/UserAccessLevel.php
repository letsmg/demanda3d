<?php

namespace App\Enums;

enum UserAccessLevel: int
{
    /**
     * Vendedor Master — Acesso total: financeiro, gerência, exclusões.
     */
    case SELLER_1 = 1;

    /**
     * Vendedor Operacional — Apenas cadastro e manutenção de produtos/catálogo.
     */
    case SELLER_2 = 2;

    /**
     * Transportador Admin — Acesso total ao painel logístico.
     */
    case CARRIER_1 = 5;

    /**
     * Transportador Colaborador — Acesso operacional limitado.
     */
    case CARRIER_2 = 6;

    /**
     * Administrador Geral da Plataforma.
     */
    case ADMIN = 10;

    /**
     * Administrador de Suporte/Operações.
     */
    case ADMIN_2 = 11;

    /**
     * Cliente final / Comprador.
     */
    case CUSTOMER = 15;

    // ── Labels ───────────────────────────────────────────────

    public function label(): string
    {
        return match ($this) {
            self::SELLER_1 => 'Vendedor Master',
            self::SELLER_2 => 'Vendedor Operacional',
            self::CARRIER_1 => 'Transportador Admin',
            self::CARRIER_2 => 'Transportador Colaborador',
            self::ADMIN => 'Administrador Geral',
            self::ADMIN_2 => 'Administrador de Suporte',
            self::CUSTOMER => 'Cliente',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SELLER_1 => 'Vendedor Master com acesso a finanças e exclusões',
            self::SELLER_2 => 'Vendedor Operacional com acesso apenas ao catálogo',
            self::CARRIER_1 => 'Transportador Admin com acesso total ao painel logístico',
            self::CARRIER_2 => 'Transportador Colaborador com acesso operacional limitado',
            self::ADMIN => 'Administrador Geral da Plataforma',
            self::ADMIN_2 => 'Administrador de Suporte — reset de senhas e bloqueios',
            self::CUSTOMER => 'Cliente final / Comprador',
        };
    }

    // ── Verificações de identidade ───────────────────────────

    public function isAdmin(): bool
    {
        return in_array($this, [self::ADMIN, self::ADMIN_2], true);
    }

    public function isAdmin1(): bool
    {
        return $this === self::ADMIN;
    }

    public function isAdmin2(): bool
    {
        return $this === self::ADMIN_2;
    }

    public function isSeller(): bool
    {
        return in_array($this, [self::SELLER_1, self::SELLER_2], true);
    }

    public function isSeller1(): bool
    {
        return $this === self::SELLER_1;
    }

    public function isSeller2(): bool
    {
        return $this === self::SELLER_2;
    }

    public function isCarrier(): bool
    {
        return in_array($this, [self::CARRIER_1, self::CARRIER_2], true);
    }

    public function isCarrier1(): bool
    {
        return $this === self::CARRIER_1;
    }

    public function isCarrier2(): bool
    {
        return $this === self::CARRIER_2;
    }

    public function isCustomer(): bool
    {
        return $this === self::CUSTOMER;
    }

    // ── Categoria (grupo) ────────────────────────────────────

    public function group(): UserAccessGroup
    {
        return UserAccessGroup::fromAccessLevel($this);
    }

    // ── Permissões ───────────────────────────────────────────

    /**
     * Acesso a finanças: SELLER_1 e ADMIN (geral).
     * ADMIN_2 e SELLER_2 NÃO têm acesso.
     */
    public function canAccessFinancials(): bool
    {
        return in_array($this, [self::SELLER_1, self::ADMIN], true);
    }

    /**
     * Gerenciamento de tenant: SELLER_1 e ADMIN (geral).
     */
    public function canManageTenant(): bool
    {
        return in_array($this, [self::SELLER_1, self::ADMIN], true);
    }

    /**
     * Acesso a conteúdo adulto: vendedores e admins.
     */
    public function canAccessAdultContent(): bool
    {
        return $this->isSeller() || $this->isAdmin();
    }

    /**
     * Pode gerenciar usuários do próprio tenant: SELLER_1 e SELLER_2.
     * SELLER_2 só pode cadastrar outros SELLER_2 (regra no Controller).
     */
    public function canManageTeam(): bool
    {
        return $this->isSeller();
    }

    /**
     * Pode bloquear/reativar usuários: ADMIN, ADMIN_2.
     */
    public function canToggleUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Pode resetar senhas: ADMIN, ADMIN_2.
     */
    public function canResetPasswords(): bool
    {
        return $this->isAdmin();
    }

    // ── Coleções ─────────────────────────────────────────────

    public static function sellerValues(): array
    {
        return [self::SELLER_1->value, self::SELLER_2->value];
    }

    public static function carrierValues(): array
    {
        return [self::CARRIER_1->value, self::CARRIER_2->value];
    }

    public static function adminValues(): array
    {
        return [self::ADMIN->value, self::ADMIN_2->value];
    }

    /**
     * Todos os níveis que acessam o painel de staff (sellers + admins).
     */
    public static function staffPanelValues(): array
    {
        return array_merge(
            self::sellerValues(),
            self::adminValues(),
        );
    }

    /**
     * Todos os valores do enum.
     */
    public static function allValues(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}