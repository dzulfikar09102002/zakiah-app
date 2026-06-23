<?php

namespace App\Services;

use App\Models\Location;
use App\Models\ProductLocationStock;
use App\Models\SaleRefund;
use App\Models\SaleTransaction;
use App\Models\SaleTransactionDetail;
use Carbon\Carbon;
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
    public function getSalesRefundSummary()
    {
        $query = SaleRefund::query();
        $startAt = request('start_at')
            ? Carbon::parse(request('start_at'))->startOfDay()
            : today()->startOfDay();

        $endAt = request('end_at')
            ? Carbon::parse(request('end_at'))->endOfDay()
            : today()->endOfDay();
        $locationIds = auth()->user()
            ->entity
            ->locations()
            ->pluck('id')
            ->toArray();

        $query->whereIn(
            'location_id',
            $locationIds
        );

        $query->where('local_sales_at', '>=', $startAt);
        $query->where('local_sales_at', '<=', $endAt);

        $selectAll = request('select_all_location') == 'true';

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
                'location_id',
                $excludeLocs
            );
        } elseif (!$selectAll && count($locs) > 0) {
            $query->whereIn(
                'location_id',
                $locs
            );
        }

        return $query
            ->selectRaw('
                SUM(net_sales_after_tax)
                as net_sales_after_tax
            ')
            ->first();
    }
    public function getSalesSummary()
    {
        $query = SaleTransaction::query();

        $locationIds = auth()->user()
            ->entity
            ->locations()
            ->pluck('id')
            ->toArray();

        $query->whereIn(
            'location_id',
            $locationIds
        );

        $query->where(
            'status',
            'ok'
        );

        $query->where(
            'sales_at',
            '>=',
            request('start_at')
                ? Carbon::parse(request('start_at'))->startOfDay()
                : today()->startOfDay()
        );

        $query->where(
            'sales_at',
            '<=',
            request('end_at')
                ? Carbon::parse(request('end_at'))->endOfDay()
                : today()->endOfDay()
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
                'location_id',
                $excludeLocs
            );
        } elseif (!$selectAll && count($locs) > 0) {
            $query->whereIn(
                'location_id',
                $locs
            );
        }

        return $query
            ->selectRaw('
                SUM(net_sales_after_tax)
                as net_sales_after_tax
            ')
            ->selectRaw('
                SUM(net_profit)
                as net_profit
            ')
            ->first();
    }

    public function getTopProductsAndCategories()
    {
        $query = SaleTransactionDetail::query();

        $locationIds = auth()->user()
            ->entity
            ->locations()
            ->pluck('id')
            ->toArray();

        $query->whereIn(
            'location_id',
            $locationIds
        );

        $query->where(
            'status',
            'ok'
        );

        $query->where(
            'local_sales_at',
            '>=',
            request('start_at')
                ? Carbon::parse(request('start_at'))->startOfDay()
                : today()->startOfDay()
        );

        $query->where(  
            'local_sales_at',
            '<=',
            request('end_at')
                ? Carbon::parse(request('end_at'))->endOfDay()
                : today()->endOfDay()
        );

        $selectAll = request('select_all_location') == 'true';

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
                'location_id',
                $excludeLocs
            );
        } elseif (!$selectAll && count($locs) > 0) {
            $query->whereIn(
                'location_id',
                $locs
            );
        }

        $products = (clone $query)
            ->select(
                'product_id',
                'product_name'
            )
            ->selectRaw('
                SUM(total_line_amount)
                as total_line_amount
            ')
            ->selectRaw('
                SUM(quantity)
                as quantity
            ')
            ->groupBy(
                'product_id',
                'product_name'
            )
            ->orderByDesc(
                'total_line_amount'
            )
            ->orderByDesc(
                'quantity'
            )
            ->limit(5)
            ->get();

        $categories = (clone $query)
            ->select(
                'product_category_id',
                'product_category_name'
            )
            ->selectRaw('
                SUM(total_line_amount)
                as total_line_amount
            ')
            ->selectRaw('
                SUM(quantity)
                as quantity
            ')
            ->groupBy(
                'product_category_id',
                'product_category_name'
            )
            ->orderByDesc(
                'total_line_amount'
            )
            ->orderByDesc(
                'quantity'
            )
            ->limit(5)
            ->get();

        return [
            'products' => $products,
            'categories' => $categories,
        ];
    }
}