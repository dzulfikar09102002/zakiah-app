<?php

namespace App\Enums;

enum CustomerCategoryResetEveryEnum: string
{
    case Daily = 'daily';
    case Weekly = 'weekly';
    case Monthly = 'monthly';
    case Annual = 'annual';
}
