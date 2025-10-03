<?php

namespace App\Enums;

enum UserRole: string
{
    case CUSTOMER = 'customer';
    case DEVELOPER = 'developer';

    public function label(): string
    {
        return match ($this) {
            self::CUSTOMER => 'Kunde',
            self::DEVELOPER => 'Entwickler',
        };
    }

    public function isDeveloper(): bool
    {
        return $this === self::DEVELOPER;
    }

    public function isCustomer(): bool
    {
        return $this === self::CUSTOMER;
    }
}