<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductLocationStock;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

class StockRemainingService
{
    public function getRemainingStock(int $locationId)
    {
        return $this->remainingStockQuery($locationId)
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    /**
     * Versi PDF memakai join (bukan relasi) karena cursor() tidak menjalankan eager loading.
     */
    public function getRemainingStockForPdf(int $locationId): LazyCollection
    {
        return $this->remainingStockQuery($locationId)
            ->setEagerLoads([])
            ->join('products', 'products.id', '=', 'product_location_stocks.product_id')
            ->leftJoin('product_categories', 'product_categories.id', '=', 'products.product_category_id')
            ->select([
                'products.sku',
                'products.barcode',
                'products.name',
                'product_categories.name as category',
                DB::raw('SUM(product_location_stocks.stock) as stock'),
                'products.cost_of_goods_sold',
                'products.sell_price',
            ])
            ->groupBy(
                'products.sku',
                'products.barcode',
                'products.name',
                'product_categories.name',
                'products.cost_of_goods_sold',
                'products.sell_price',
            )
            ->limit(ReportPdfService::MAX_ROWS + 1)
            ->toBase()
            ->cursor()
            ->map(fn (object $row) => [
                'sku' => $row->sku,
                'barcode' => $row->barcode,
                'name' => $row->name,
                'category' => $row->category,
                'stock' => (int) $row->stock,
                'cost_of_goods_sold' => (int) $row->cost_of_goods_sold,
                'sell_price' => (int) $row->sell_price,
            ]);
    }

    private function remainingStockQuery(int $locationId): Builder
    {
        $entityId = auth()->user()?->entity?->id;
        $product_category_id = request('product_category_id', 'all');
        $search = request('search', '');

        return ProductLocationStock::query()
            ->selectRaw('MIN(product_location_stocks.id) as id, product_location_stocks.product_id, product_location_stocks.location_id, SUM(product_location_stocks.stock) as stock')
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
            ->where('product_location_stocks.location_id', $locationId)
            ->groupBy('product_location_stocks.product_id', 'product_location_stocks.location_id')
            ->havingRaw('SUM(product_location_stocks.stock) > 0')
            ->orderByDesc('stock');
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
                'pls.stock',
            ])
            ->leftJoinSub(
                ProductLocationStock::query()
                    ->select('product_id')
                    ->selectRaw('SUM(stock) as stock')
                    ->where('location_id', $locationId)
                    ->groupBy('product_id'),
                'pls',
                'products.id',
                '=',
                'pls.product_id'
            )
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
