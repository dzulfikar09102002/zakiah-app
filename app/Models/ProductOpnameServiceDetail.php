<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOpnameServiceDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'product_unit_id',
        'product_category_id',
        'recorded_stock',
        'counted_stock',
        'difference_stock',
        'note',
    ];

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }

    public function productUnit(): BelongsTo {
        return $this->belongsTo(ProductUnit::class);
    }

    public function productCategory(): BelongsTo {
        return $this->belongsTo(ProductCategory::class);
    }
}
