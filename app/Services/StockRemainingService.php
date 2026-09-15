<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductLocationStock;
use Illuminate\Support\Facades\DB;

class StockRemainingService
{
    public function getRemainingStock(int $locationId)
    {
        $entityId = auth()->user()?->entity?->id;
        $product_category_id = request('product_category_id', 'all');
        $search = request('search', '');
        $query = ProductLocationStock::query()
            ->with([
                'product',
                'product.productCategory:id,name',
                'location:id,name',
            ])
            ->whereHas('product', function ($q) use ($entityId, $search, $product_category_id) {
                $q->where('entity_id', $entityId)
                    ->when($search, fn ($q) => $q->where('name', 'like', "%$search%"))
                    ->when($product_category_id !== 'all', fn ($q) => $q->where('product_category_id', $product_category_id));
            })

            ->whereHas('location', function ($q) use ($entityId) {
                $q->where('entity_id', $entityId);
            })
            ->where('location_id', $locationId)
            ->where('stock', '>', 1)
            ->orderByDesc('stock');

        return $query
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function getLocations()
    {
        return Location::where('entity_id', auth()->user()?->entity?->id)->get();
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
    public function getAllStockForExport(int $locationId)
    {
        ini_set('memory_limit', '512M');
        
        if (function_exists('db') && method_exists(db(), 'disableQueryLog')) {
            DB::disableQueryLog();
        }

        $entityId = auth()->user()?->entity?->id;
        $product_category_id = request('product_category_id', 'all');

        return Product::query()
            ->select([
                'products.id',
                'products.sku',
                'products.barcode',
                'products.name',
                'products.product_category_id',
                'products.cost_of_goods_sold',
                'products.last_buying_price',
                'products.sell_price',
                'pls.stock'
            ])
            ->leftJoin('product_location_stocks as pls', function ($join) use ($locationId) {
                $join->on('products.id', '=', 'pls.product_id')
                    ->where('pls.location_id', '=', $locationId);
            })
            ->with(['productCategory:id,name'])
            ->where('products.entity_id', $entityId)
            ->when($product_category_id !== 'all', function ($q) use ($product_category_id) {
                $q->where('products.product_category_id', $product_category_id);
            })
            ->orderByRaw('COALESCE(pls.stock, 0) DESC')
            ->cursor()
            ->map(function ($product) {
                return [
                    'SKU' => $product->sku ?? '-',
                    'Barcode' => $product->barcode ?? '-',
                    'Nama' => $product->name ?? '-',
                    'Kategori' => $product->productCategory?->name ?? '-',
                    'Stok' => (int) ($product->stock ?? 0),
                    'HPP' => $product->cost_of_goods_sold ?? 0,
                    'Harga Beli' => $product->last_buying_price ?? 0,
                    'Harga Jual' => $product->sell_price ?? 0,
                ];
            });
    }
}
