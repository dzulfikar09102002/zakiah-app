<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleRefund extends Model
{
    use HasFactory;
    
    protected function casts(): array
    {
        return [
            'product_ids' => 'array',
            'product_category_ids' => 'array',
            'modifier_ids' => 'array',
            'modifier_option_ids' => 'array',
        ];
    }

    public function saleRefundDetails(): HasMany {
        return $this->hasMany(SaleRefundDetail::class);
    }
}
