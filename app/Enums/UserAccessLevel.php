<?php

namespace App\Enums;

enum UserAccessLevel: int
{
    case OPERATIONAL = 0;
    case MANAGEMENT = 1;
    case CUSTOMER = 5;
    case ADMIN = 10;

    public function label(): string
    {
        return match ($this) {
            self::OPERATIONAL => 'Operational',
            self::MANAGEMENT => 'Management',
            self::CUSTOMER => 'Customer',
            self::ADMIN => 'Administrator',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::OPERATIONAL => 'Operacional com acesso limitado',
            self::MANAGEMENT => 'Gestor com acesso a relatórios e finanças',
            self::CUSTOMER => 'Cliente com acesso apenas aos próprios dados',
            self::ADMIN => 'Administrador com acesso total ao SaaS',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function isManagement(): bool
    {
        return $this === self::MANAGEMENT;
    }

    public function isOperational(): bool
    {
        return $this === self::OPERATIONAL;
    }

    public function isCustomer(): bool
    {
        return $this === self::CUSTOMER;
    }

    public function isStaff(): bool
    {
        return in_array($this, [self::OPERATIONAL, self::MANAGEMENT, self::ADMIN], true);
    }

    public function canAccessFinancials(): bool
    {
        return in_array($this, [self::MANAGEMENT, self::ADMIN], true);
    }

    public function canManageTenant(): bool
    {
        return in_array($this, [self::MANAGEMENT, self::ADMIN], true);
    }

    /**
     * Staff (Admin, Management, Operational) sempre pode acessar conteúdo adulto.
     * A verificação de idade para Customers é feita no Model User.
     */
    public function canAccessAdultContent(): bool
    {
        return $this->isStaff();
    }

    public function group(): UserAccessGroup
    {
        return UserAccessGroup::fromAccessLevel($this);
    }

    /**
     * Retorna os valores inteiros de todos os níveis de acesso Staff (Admin, Management, Operational).
     * Uso principal: queries whereIn('access_level', ...).
     *
     * @return int[]
     */
    public static function staffValues(): array
    {
        return [
            self::OPERATIONAL->value,
            self::MANAGEMENT->value,
            self::ADMIN->value,
        ];
    }

    /**
     * Retorna os valores inteiros de todos os níveis de acesso.
     *
     * @return int[]
     */
    public static function allValues(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }
}