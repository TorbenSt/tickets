<?php

namespace App\Enums;

enum TicketPriority: string
{
    case ÜBERPRÜFUNG = 'überprüfung';
    case NORMAL = 'normal';
    case ASAP = 'asap';
    case NOTFALL = 'notfall';

    public function label(): string
    {
        return match ($this) {
            self::ÜBERPRÜFUNG => 'Überprüfung',
            self::NORMAL => 'Normal',
            self::ASAP => 'ASAP',
            self::NOTFALL => 'Notfall',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ÜBERPRÜFUNG => 'gray',
            self::NORMAL => 'blue',
            self::ASAP => 'orange',
            self::NOTFALL => 'red',
        };
    }

    public function order(): int
    {
        return match ($this) {
            self::NOTFALL => 4,
            self::ASAP => 3,
            self::NORMAL => 2,
            self::ÜBERPRÜFUNG => 1,
        };
    }
}