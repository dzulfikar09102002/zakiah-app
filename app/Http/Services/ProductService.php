<?php

namespace App\Http\Services;

use App\Models\Product;
use App\Models\ProductCategory;

class ProductService
{
    public function getProducts(int $entityId)
    {
        $perPage = request('per_page', 10);
        $search = request('search', '');

        return Product::with('product_category')->where('entity_id', $entityId)
            ->whereLike('name', "%$search%")
            ->withSum('locationStocks as total_stock', 'stock')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getCategories(int $entityId)
    {
        return ProductCategory::where('entity_id', $entityId)->get();
    }
}
