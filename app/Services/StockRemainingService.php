<?php

namespace App\Services;

use App\Models\Location;
use App\Models\ProductCategory;
use App\Models\ProductLocationStock;

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
    $entityId = auth()->user()?->entity?->id;
    $product_category_id = request('product_category_id', 'all');

    $query = ProductLocationStock::query()
        ->with(['product', 'product.productCategory'])
        ->whereHas('product', function ($q) use ($entityId, $product_category_id) {
            $q->where('entity_id', $entityId)
              ->when($product_category_id !== 'all', fn($q) => $q->where('product_category_id', $product_category_id));
        })
        ->whereHas('location', function ($q) use ($entityId) {
            $q->where('entity_id', $entityId);
        })
        ->where('location_id', $locationId)
        ->orderByDesc('stock');

    $stocks = $query->get();

    return $stocks->map(function ($pls) {
        return [
            'SKU' => $pls->product?->sku ?? '-',
            'Barcode' => $pls->product?->barcode ?? '-',
            'Nama' => $pls->product?->name ?? '-',
            'Kategori' => $pls->product->productCategory?->name ?? '-',
            'Stok' => $pls->stock,
            'HPP' => $pls->product?->cost_of_goods_sold ?? 0,
            'Harga Beli' => $pls->product?->last_buying_price ?? 0,
            'Harga Jual' => $pls->product?->sell_price ?? 0,
        ];
    });
}
}
