<?php

namespace App\Helpers;

class PercentageCalculation
{
    public static function calculate(int $amount, int $percentage, int $maxAmount = null): int
    {
        $discountedAmount = $amount * $percentage / 100;
        if ($maxAmount != null && $discountedAmount > $maxAmount) {
            $discountedAmount = $maxAmount;
        }

        return $discountedAmount;
    }
}
