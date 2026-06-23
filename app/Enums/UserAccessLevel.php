<?php

namespace App\Enums;

enum UserAccessLevel: int
{
    case PARTNER = 0;
    case ADMIN = 1;
    case OPERATIONAL = 5;
    case CUSTOMER = 9;

    public function label(): string
    {
        return match($this) {
            self::PARTNER => 'Partner',
            self::ADMIN => 'Administrator',
            self::OPERATIONAL => 'Operational',
            self::CUSTOMER => 'Customer',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::PARTNER => 'Sócio com acesso total ao SaaS',
            self::ADMIN => 'Administrador com acesso total',
            self::OPERATIONAL => 'Operacional com acesso limitado',
            self::CUSTOMER => 'Cliente com acesso apenas aos próprios dados',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function isPartner(): bool
    {
        return $this === self::PARTNER;
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
        return in_array($this, [self::ADMIN, self::PARTNER, self::OPERATIONAL], true);
    }

    public function group(): UserAccessGroup
    {
        return UserAccessGroup::fromAccessLevel($this);
    }
}