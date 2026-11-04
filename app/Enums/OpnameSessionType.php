<?php

namespace App\Enums;

enum OpnameSessionType: int
{
    case Opname = 1;
    case Revision = 2;

    public function label(): string
    {
        return match ($this) {
            self::Opname => 'Stok Opname',
            self::Revision => 'Revisi Stok Opname',
        };
    }
}
