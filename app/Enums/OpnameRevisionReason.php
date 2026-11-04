<?php

namespace App\Enums;

enum OpnameRevisionReason: int
{
    case Misplaced = 1;
    case Damaged = 2;
    case Lost = 3;

    public function label(): string
    {
        return match ($this) {
            self::Misplaced => 'Ketelisut',
            self::Damaged => 'Rusak',
            self::Lost => 'Hilang',
        };
    }
}
