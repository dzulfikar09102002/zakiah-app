<?php

namespace App\Http\Services;

use App\Models\Product;
use App\Models\ProductCategory;

class ProductService
{
    public function getProducts(int $entityId)
    {
        $search = request('search', '');
        $product_category_id = request('product_category_id', 'all');
        $query = Product::with('product_category')->where('entity_id', $entityId)
            ->whereLike('name', "%$search%")
            ->orWhereLike('code', "%$search%")
            ->withSum('locationStocks as total_stock', 'stock');

        return ($product_category_id === 'all' ? $query : $query->where('product_category_id', $product_category_id))
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function getCategories(int $entityId)
    {
        return ProductCategory::where('entity_id', $entityId)->get()->map(function ($category) {
            return [
                'value' => $category->id,
                'label' => $category->name,
            ];
        });
    }
}
