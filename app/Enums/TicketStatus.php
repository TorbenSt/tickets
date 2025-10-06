<?php

namespace App\Enums;

enum TicketStatus: string
{   
    case OPEN = 'open';
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case REVIEW = 'review';
    case DONE = 'done';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'Benötigt Bestätigung',
            self::TODO => 'To Do',
            self::IN_PROGRESS => 'In Bearbeitung',
            self::REVIEW => 'Review',
            self::DONE => 'Fertig',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::OPEN => 'orange',
            self::TODO => 'gray',
            self::IN_PROGRESS => 'blue',
            self::REVIEW => 'yellow',
            self::DONE => 'green',
        };
    }
}