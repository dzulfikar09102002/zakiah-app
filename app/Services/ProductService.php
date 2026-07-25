<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductLocationStock;
use App\Models\ProductUnit;
use App\Models\Supplier;

class ProductService
{
    public function getProducts()
    {
        $search = request('search', '');
        $product_category_id = request('product_category_id', 'all');
        $query = Product::with('productCategory', 'productLocationStocks', 'supplier')
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
        ->orderByDesc('updated_at')
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }
    public function getSuppliersName()
    {
        return Supplier::
        where('entity_id', auth()->user()?->entity?->id)
        ->pluck('name')
            ->toArray();
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

    public function getCurrentStockMap(array $skus): array
    {
        if (empty($skus)) {
            return [];
        }

        $entityId = auth()->user()?->entity?->id;

        return ProductLocationStock::query()
            ->select('product_location_stocks.location_id', 'product_location_stocks.stock', 'products.sku')
            ->join('products', 'products.id', '=', 'product_location_stocks.product_id')
            ->where('products.entity_id', $entityId)
            ->whereIn('products.sku', $skus)
            ->get()
            ->groupBy('sku')
            ->map(fn ($rows) => $rows->pluck('stock', 'location_id')->toArray())
            ->toArray();
    }
}
