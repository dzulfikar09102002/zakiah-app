<?php

namespace App\Enums;

enum PromoRewardTemplateEnum: string
{
    case DiscountPercentage = 'discount_percentage';
    case DiscountFixed = 'discount_fixed';
    case GetProduct = 'get_product';
    case SpecialPrice = 'special_price';
}
