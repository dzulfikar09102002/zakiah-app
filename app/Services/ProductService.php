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
        $query = Product::with('productCategory')
            ->where('entity_id', auth()->user()?->entity?->id)
            ->where(function ($q) use ($search) {
                $q->whereLike('name', "%$search%")
                    ->orWhereLike('code', "%$search%")
                    ->orWhereLike('sku', "%$search%")
                    ->orWhereLike('barcode', "%$search%");
            })
            ->withSum('productLocationStocks as total_stock', 'stock');

        if ($product_category_id !== 'all') {
            $query->where('product_category_id', $product_category_id);
        }

        return $query
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function getCategoryOptions()
    {
        $options = ProductCategory::where('entity_id', auth()->user()?->entity?->id)->get()
            ->map(function ($category) {
                return [
                    'value' => $category->id,
                    'label' => $category->name,
                ];
            });

        $options->prepend([
            'value' => 'all',
            'label' => 'Semua kategori',
        ]);

        return $options;
    }
}
