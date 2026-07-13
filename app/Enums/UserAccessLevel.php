<?php

namespace App\Enums;

enum UserAccessLevel: int
{
    /**
     * Vendedor Master — Acesso total: financeiro, gerência, exclusões.
     * (Antigo: management / 1)
     */
    case SELLER_1 = 1;

    /**
     * Vendedor Operacional — Apenas cadastro e manutenção de produtos/catálogo.
     * (Antigo: operational / 0)
     */
    case SELLER_2 = 2;

    /**
     * Transportador Admin — Acesso total ao painel logístico.
     */
    case CARRIER_1 = 5;

    /**
     * Transportador Motorista — Acesso apenas à leitura de viagens.
     */
    case CARRIER_2 = 6;

    /**
     * Administrador Global da Plataforma.
     */
    case ADMIN = 10;

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
            self::CARRIER_2 => 'Transportador Motorista',
            self::ADMIN => 'Administrador',
            self::CUSTOMER => 'Cliente',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::SELLER_1 => 'Vendedor Master com acesso a finanças e exclusões',
            self::SELLER_2 => 'Vendedor Operacional com acesso apenas ao catálogo',
            self::CARRIER_1 => 'Transportador Admin com acesso total ao painel logístico',
            self::CARRIER_2 => 'Transportador Motorista com acesso apenas à leitura de viagens',
            self::ADMIN => 'Administrador Global da Plataforma',
            self::CUSTOMER => 'Cliente final / Comprador',
        };
    }

    // ── Verificações de identidade ───────────────────────────

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
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
     * Acesso a finanças: apenas SELLER_1 e ADMIN.
     */
    public function canAccessFinancials(): bool
    {
        return in_array($this, [self::SELLER_1, self::ADMIN], true);
    }

    /**
     * Gerenciamento de tenant: SELLER_1 e ADMIN.
     */
    public function canManageTenant(): bool
    {
        return in_array($this, [self::SELLER_1, self::ADMIN], true);
    }

    /**
     * Acesso a conteúdo adulto: vendedores e admin.
     */
    public function canAccessAdultContent(): bool
    {
        return $this->isSeller() || $this->isAdmin();
    }

    // ── Coleções ─────────────────────────────────────────────

    /**
     * Todos os níveis de vendedores (publicam produtos).
     */
    public static function sellerValues(): array
    {
        return [self::SELLER_1->value, self::SELLER_2->value];
    }

    /**
     * Todos os níveis de transportadores.
     */
    public static function carrierValues(): array
    {
        return [self::CARRIER_1->value, self::CARRIER_2->value];
    }

    /**
     * Todos os níveis que acessam o painel de staff (sellers + admin).
     */
    public static function staffPanelValues(): array
    {
        return [self::SELLER_1->value, self::SELLER_2->value, self::ADMIN->value];
    }

    /**
     * Todos os valores do enum.
     *
     * @return int[]
     */
    public static function allValues(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}