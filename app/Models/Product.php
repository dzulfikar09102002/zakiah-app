<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $casts = [
        'total_stock' => 'integer',
    ];

    public function productCategory()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function locationStocks()
    {
        return $this->hasMany(ProductLocationStock::class, 'product_id', 'id');
    }
}
