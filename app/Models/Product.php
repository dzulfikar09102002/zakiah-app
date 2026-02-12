<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $casts = [
        'total_stock' => 'integer',
    ];

    public function product_category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function locationStocks()
    {
        return $this->hasMany(ProductLocationStock::class, 'product_id', 'id');
    }
}
