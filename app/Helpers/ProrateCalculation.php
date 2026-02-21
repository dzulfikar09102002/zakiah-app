<?php

namespace App\Helpers;

class ProrateCalculation
{
    public static function calculate(int $lineAmount, int $totalAmount, int $amount): int
    {
        if ($totalAmount == 0) {
            return 0;
        }

        return $amount * $lineAmount / $totalAmount;
    }
}
