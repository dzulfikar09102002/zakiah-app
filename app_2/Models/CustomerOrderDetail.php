<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerOrderDetail extends Model
{
    use HasFactory;

    public function customerOrder(): BelongsTo {
        return $this->belongsTo(CustomerOrder::class);
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

    public function productCategory(): BelongsTo {
        return $this->belongsTo(ProductCategory::class);
    }

    public function promo(): BelongsTo {
        return $this->belongsTo(Promo::class);
    }

    protected $fillable = [
        'id',
    ];

    protected function casts(): array
    {
        return [
            'adjustment' => 'array',
            'custom_price' => 'bool',
        ];
    }
}
