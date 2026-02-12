<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function locationStocks()
{
    return $this->hasMany(ProductLocationStock::class, 'product_id', 'id');
}
}
