<?php

namespace App\Helpers;

class NumberFormat
{
    public static function money(float $amount, int $decimal = 0): string
    {
        return number_format($amount, $decimal, ',', '.');
    }
}
