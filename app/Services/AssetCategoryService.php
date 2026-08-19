<?php

namespace App\Services;

use App\Models\Location;
use App\Models\ProductCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssetCategoryService
{
    public function getLocationOptions()
    {
        return Location::query()
            ->where('entity_id', auth()->user()?->entity?->id)
            ->where('status', 'active')
            ->orderBy('name', 'asc')
            ->get()
            ->map(fn (Location $location) => [
                'label' => Str::title(Str::lower($location->name)),
                'value' => $location->id,
            ]);
    }

    public function getCategoryAssets()
    {
        $search = request('search', '');
        $entityId = auth()->user()?->entity?->id;

        $selectAll = request('select_all_location') !== '0';
        $locs = array_map('intval', (array) request()->input('locs', []));
        $excludeLocs = array_map('intval', (array) request()->input('exclude_locs', []));

        $allowedLocationIds = auth()->user()
            ?->entity
            ?->locations()
            ?->pluck('id')
            ?->toArray() ?? [];

        $query = ProductCategory::query()
            ->where('product_categories.entity_id', $entityId)
            ->where('product_categories.status', 'active')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->whereLike('product_categories.name', "%$search%")
                        ->orWhereLike('product_categories.search_name', "%$search%");
                });
            })
            ->leftJoin('products', function ($join) {
                $join->on('products.product_category_id', '=', 'product_categories.id')
                    ->whereNull('products.deleted_at')
                    ->where('products.status', '=', 'active');
            })
            ->leftJoin('product_location_stocks', function ($join) use ($selectAll, $locs, $excludeLocs, $allowedLocationIds) {
                $join->on('product_location_stocks.product_id', '=', 'products.id')
                    ->whereNull('product_location_stocks.deleted_at');

                if (!empty($allowedLocationIds)) {
                    $join->whereIn('product_location_stocks.location_id', $allowedLocationIds);
                }

                if ($selectAll && count($excludeLocs) > 0) {
                    $join->whereNotIn('product_location_stocks.location_id', $excludeLocs);
                } elseif (!$selectAll && count($locs) > 0) {
                    $join->whereIn('product_location_stocks.location_id', $locs);
                } elseif (!$selectAll && empty($locs)) {
                    $join->whereRaw('1 = 0');
                }
            })
            ->select([
                'product_categories.id',
                'product_categories.name',
                DB::raw('COUNT(DISTINCT products.id) as total_products'),
                DB::raw('COALESCE(SUM(CAST(product_location_stocks.stock AS SIGNED)), 0) as total_stock'),
                DB::raw('COALESCE(SUM(CAST(product_location_stocks.stock AS SIGNED) * CAST(products.last_buying_price AS SIGNED)), 0) as total_buying_asset'),
                DB::raw('COALESCE(SUM(CAST(product_location_stocks.stock AS SIGNED) * CAST(products.sell_price AS SIGNED)), 0) as total_selling_asset'),
            ])
            ->groupBy('product_categories.id', 'product_categories.name')
            ->orderBy('product_categories.name', 'asc');

        return $query
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function getAssetSummary()
    {
        $entityId = auth()->user()?->entity?->id;
        $selectAll = request('select_all_location') !== '0';
        $locs = array_map('intval', (array) request()->input('locs', []));
        $excludeLocs = array_map('intval', (array) request()->input('exclude_locs', []));

        $allowedLocationIds = auth()->user()
            ?->entity
            ?->locations()
            ?->pluck('id')
            ?->toArray() ?? [];

        return ProductCategory::query()
            ->where('product_categories.entity_id', $entityId)
            ->where('product_categories.status', 'active')
            ->leftJoin('products', function ($join) {
                $join->on('products.product_category_id', '=', 'product_categories.id')
                    ->whereNull('products.deleted_at')
                    ->where('products.status', '=', 'active');
            })
            ->leftJoin('product_location_stocks', function ($join) use ($selectAll, $locs, $excludeLocs, $allowedLocationIds) {
                $join->on('product_location_stocks.product_id', '=', 'products.id')
                    ->whereNull('product_location_stocks.deleted_at');

                if (!empty($allowedLocationIds)) {
                    $join->whereIn('product_location_stocks.location_id', $allowedLocationIds);
                }

                if ($selectAll && count($excludeLocs) > 0) {
                    $join->whereNotIn('product_location_stocks.location_id', $excludeLocs);
                } elseif (!$selectAll && count($locs) > 0) {
                    $join->whereIn('product_location_stocks.location_id', $locs);
                } elseif (!$selectAll && empty($locs)) {
                    $join->whereRaw('1 = 0');
                }
            })
            ->select([
                DB::raw('COUNT(DISTINCT product_categories.id) as total_categories'),
                DB::raw('COUNT(DISTINCT products.id) as total_products'),
                DB::raw('COALESCE(SUM(CAST(product_location_stocks.stock AS SIGNED)), 0) as grand_total_stock'),
                DB::raw('COALESCE(SUM(CAST(product_location_stocks.stock AS SIGNED) * CAST(products.last_buying_price AS SIGNED)), 0) as grand_buying_asset'),
                DB::raw('COALESCE(SUM(CAST(product_location_stocks.stock AS SIGNED) * CAST(products.sell_price AS SIGNED)), 0) as grand_selling_asset'),
            ])
            ->first();
    }
}