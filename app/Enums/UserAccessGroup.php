<?php

namespace App\Enums;

enum UserAccessGroup: string
{
    case STAFF = 'staff';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::STAFF => 'Equipe',
            self::CUSTOMER => 'Cliente',
        };
    }

    public static function fromAccessLevel(UserAccessLevel $level): self
    {
        return match ($level) {
            UserAccessLevel::ADMIN,
            UserAccessLevel::PARTNER,
            UserAccessLevel::OPERATIONAL => self::STAFF,
            UserAccessLevel::CUSTOMER => self::CUSTOMER,
        };
    }
}