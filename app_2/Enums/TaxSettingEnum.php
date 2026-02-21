<?php

namespace App\Enums;

enum TaxSettingEnum: string
{
    case ProductIncludeTax = 'product_include_tax';
    case ProductExcludeTax = 'product_exclude_tax';
}
