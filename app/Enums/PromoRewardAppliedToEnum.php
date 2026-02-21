<?php

namespace App\Enums;

enum PromoRewardAppliedToEnum: string
{
    case TotalOrder = 'total_order';
    case Product = 'product';
    case ProductCategory = 'product_category';
}
