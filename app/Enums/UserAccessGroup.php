<?php

namespace App\Enums;

/**
 * Grupos de acesso usados por Middlewares e redirecionamentos de portais.
 *
 * Mapeamento:
 *   PLATFORM_ADMIN → ADMIN
 *   SELLERS        → SELLER_1, SELLER_2
 *   CARRIERS       → CARRIER_1, CARRIER_2
 *   CUSTOMER       → CUSTOMER
 */
enum UserAccessGroup: string
{
    case PLATFORM_ADMIN = 'platform_admin';
    case SELLERS = 'sellers';
    case CARRIERS = 'carriers';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::PLATFORM_ADMIN => 'Administrador da Plataforma',
            self::SELLERS => 'Vendedores',
            self::CARRIERS => 'Transportadores',
            self::CUSTOMER => 'Cliente',
        };
    }

    /**
     * Retorna o grupo correspondente a um nível de acesso.
     */
    public static function fromAccessLevel(UserAccessLevel $level): self
    {
        return match ($level) {
            UserAccessLevel::ADMIN => self::PLATFORM_ADMIN,
            UserAccessLevel::SELLER_1,
            UserAccessLevel::SELLER_2 => self::SELLERS,
            UserAccessLevel::CARRIER_1,
            UserAccessLevel::CARRIER_2 => self::CARRIERS,
            UserAccessLevel::CUSTOMER => self::CUSTOMER,
        };
    }
}