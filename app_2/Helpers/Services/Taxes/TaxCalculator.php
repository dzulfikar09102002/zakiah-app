<?php

namespace App\Helpers\Services\Taxes;

use App\Enums\TaxSettingEnum;
use App\Helpers\Data\Tax\TaxCalculated;

class TaxCalculator
{
    public static function calculate(int $amount, ?int $taxRate, ?TaxSettingEnum $taxSetting): TaxCalculated
    {
        $taxRate = $taxRate ?? 0;
        $taxSetting = $taxSetting ?? TaxSettingEnum::ProductExcludeTax;

        $amountBeforeTax = $amount;
        if ($taxSetting == TaxSettingEnum::ProductIncludeTax) {
            $taxAmount = $amount * ($taxRate / (100 + $taxRate));
            $amountBeforeTax = $amount - $taxAmount;
        } else {
            $taxAmount = $amount * ($taxRate / 100);
        }

        $taxAmount = round($taxAmount, 2) * 100;
        $amountBeforeTax = round($amountBeforeTax, 2) * 100;

        return (new TaxCalculated)->setPrice($amount)->setTaxAmount($taxAmount)->setPriceBeforeTax($amountBeforeTax);
    }
}
