<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCategory;

class ProductService
{
    public function getProducts()
    {
        $search = request('search', '');
        $product_category_id = request('product_category_id', 'all');
        $query = Product::with('product_category')
        ->where('entity_id', auth()->user()?->entity?->id)
        ->where(function ($q) use ($search) {
            $q->whereLike('name', "%$search%")
            ->orWhereLike('code', "%$search%");
        })
        ->withSum('locationStocks as total_stock', 'stock');

        if ($product_category_id !== 'all') {
            $query->where('product_category_id', $product_category_id);
        }

        return $query
            ->paginate(request('per_page', 10))
            ->withQueryString();
        }

    public function getCategories()
    {
        return ProductCategory::where('entity_id', auth()->user()?->entity?->id)->get()->map(function ($category) {
            return [
                'value' => $category->id,
                'label' => $category->name,
            ];
        });
    }
}
