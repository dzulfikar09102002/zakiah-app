<?php

namespace App\Http\Services;

use App\Models\Product;
use App\Models\ProductCategory;

class ProductService
{
    public function getProducts(int $entityId, int $perPage = 10)
    {
        return Product::where('entity_id', $entityId)
        ->withSum('locationStocks as total_stock', 'stock')
        ->paginate($perPage)
        ->withQueryString();
    }

    public function getCategories(int $entityId)
    {
        return ProductCategory::where('entity_id', $entityId);
    }
}
