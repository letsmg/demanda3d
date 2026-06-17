<?php

namespace App\Enums;

enum UserAccessLevel: int
{
    case PARTNER = 0;
    case ADMIN = 1;
    case CUSTOMER = 9;

    public function label(): string
    {
        return match($this) {
            self::PARTNER => 'Partner',
            self::ADMIN => 'Administrator',
            self::CUSTOMER => 'Customer',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::PARTNER => 'Sócio com acesso total ao SaaS',
            self::ADMIN => 'Administrador com acesso total',
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

    public function isCustomer(): bool
    {
        return $this === self::CUSTOMER;
    }
}