<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;

class ProductService
{
    public function getProducts()
    {
        $search = request('search', '');
        $product_category_id = request('product_category_id', 'all');
        $query = Product::with('productCategory', 'productLocationStocks')
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
        return ProductCategory::where('entity_id', auth()->user()?->entity?->id)
            ->select('id as value', 'name as label')
            ->get();
    }

    public function getLocationOptions()
    {
        return Location::where('entity_id', auth()->user()?->entity?->id)
            ->select('id as value', 'name as label')
            ->get();
    }

    public function getProuductUnitOptions()
    {
        return ProductUnit::where('entity_id', auth()->user()?->entity?->id)
            ->select('id as value', 'name as label')
            ->get();
    }
}
