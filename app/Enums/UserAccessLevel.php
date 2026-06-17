<?php

namespace App\Enums;

enum UserAccessLevel: int
{
    case STAFF = 0;
    case ADMIN = 1;
    case CUSTOMER = 9;

    public function label(): string
    {
        return match($this) {
            self::STAFF => 'Staff',
            self::ADMIN => 'Administrator',
            self::CUSTOMER => 'Customer',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::STAFF => 'Funcionário com acesso limitado',
            self::ADMIN => 'Administrador com acesso total',
            self::CUSTOMER => 'Cliente externo',
        };
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }

    public function isStaff(): bool
    {
        return $this === self::STAFF;
    }

    public function isCustomer(): bool
    {
        return $this === self::CUSTOMER;
    }
}
