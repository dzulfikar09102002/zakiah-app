<?php

namespace App\Models;

use App\Observers\SaleTransactionDetailObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([SaleTransactionDetailObserver::class])]
class SaleTransactionDetail extends Model
{
    use HasFactory;

    protected $casts = [
        'quantity' => 'integer',
        'net_sales_after_tax' => 'integer',
        'payment_platform_fee' => 'integer',
        'discount_amount' => 'integer',
        'promo_amount' => 'integer',
    ];

    public function saleTransaction(): BelongsTo {
        return $this->belongsTo(SaleTransaction::class);
    }

    public function orderType(): BelongsTo {
        return $this->belongsTo(OrderType::class);
    }
    
    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }
    
    public function productUnit(): BelongsTo {
        return $this->belongsTo(ProductUnit::class);
    }
    
    public function tax(): BelongsTo {
        return $this->belongsTo(Tax::class);
    }
    
    public function productCategory(): BelongsTo {
        return $this->belongsTo(ProductCategory::class);
    }
    
    public function location(): BelongsTo {
        return $this->belongsTo(Location::class);
    }
    
    protected function casts(): array
    {
        return [
            'modifier_ids' => 'array',
            'modifier_option_ids' => 'array',
        ];
    }
}
