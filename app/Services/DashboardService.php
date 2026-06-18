<?php

namespace App\Services;

use App\Models\Location;
use App\Models\ProductLocationStock;
use Illuminate\Support\Str;

class DashboardService
{
    public function getLocationOptions()
    {
        return (new LocationService)->getLocations()->get()->map(fn (Location $location) => [
            'label' => Str::title(Str::lower($location->name)),
            'value' => $location->id,
        ]);
    }
    public function getProfitPotential()
    {
        $query = ProductLocationStock::query()
            ->join(
                'products',
                'products.id',
                '=',
                'product_location_stocks.product_id'
            );

        $locationIds = auth()->user()
            ->entity
            ->locations()
            ->pluck('id')
            ->toArray();

        $query->whereIn(
            'product_location_stocks.location_id',
            $locationIds
        );

        $selectAll = request('select_all_location') == '1';

        $locs = array_map(
            'intval',
            (array) request('locs', [])
        );

        $excludeLocs = array_map(
            'intval',
            (array) request('exclude_locs', [])
        );

        if ($selectAll && count($excludeLocs) > 0) {
            $query->whereNotIn(
                'product_location_stocks.location_id',
                $excludeLocs
            );
        } elseif (!$selectAll && count($locs) > 0) {
            $query->whereIn(
                'product_location_stocks.location_id',
                $locs
            );
        }

        return $query
            ->selectRaw('
                SUM(product_location_stocks.stock)
                as stock
            ')
            ->selectRaw('
                SUM(
                    product_location_stocks.stock
                    * CAST(products.last_buying_price AS SIGNED)
                ) as cogs
            ')
            ->selectRaw('
                SUM(
                    product_location_stocks.stock
                    * CAST(products.sell_price AS SIGNED)
                ) as sell_price
            ')
            ->first();
    }
}