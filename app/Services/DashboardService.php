<?php

namespace App\Services;

use App\Models\Location;
use App\Models\ProductLocationStock;
use App\Models\SaleRefund;
use App\Models\SaleTransaction;
use App\Models\SaleTransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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

    public function getTopLocations()
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

        return $query
            ->select(
                'location_name'
            )
            ->selectRaw(
                'SUM(net_sales_after_tax) as net_sales_after_tax'
            )
            ->groupBy(
                'location_name'
            )
            ->orderByDesc(
                'net_sales_after_tax'
            )
            ->limit(5)
            ->get();
    }
    public function getSalesByDate()
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

        return $query
            ->select(
                DB::raw("DATE_FORMAT(local_sales_at, '%Y-%m-%d') as local_sales_date")
            )
            ->selectRaw('SUM(net_sales_after_tax) as net_sales_after_tax')
            ->selectRaw('SUM(net_profit) as net_profit')
            ->groupBy('local_sales_date')
            ->orderBy('local_sales_date')
            ->limit(30)
            ->get();
    }
    public function getMonthlySales()
    {
        $query = SaleTransaction::query();

        $locationIds = auth()->user()
            ->entity
            ->locations()
            ->pluck('id')
            ->toArray();

        $query->whereIn('location_id', $locationIds);
        $query->where('status', 'ok');

        $year = request('monthly_year', now()->year);

        $query->whereRaw(
            "DATE_FORMAT(local_sales_at, '%Y') = ?",
            [$year]
        );

        $selectAll = request('select_all_location') == 'true';

        $locs = array_map('intval', (array) request('locs', []));
        $excludeLocs = array_map('intval', (array) request('exclude_locs', []));

        if ($selectAll && count($excludeLocs) > 0) {
            $query->whereNotIn('location_id', $excludeLocs);
        } elseif (!$selectAll && count($locs) > 0) {
            $query->whereIn('location_id', $locs);
        }

        $data = $query
            ->selectRaw("MONTH(local_sales_at) as month")
            ->selectRaw("SUM(net_sales_after_tax) as sales")
            ->selectRaw("SUM(net_profit) as profit")
            ->groupByRaw("MONTH(local_sales_at)")
            ->orderByRaw("MONTH(local_sales_at)")
            ->get();

        $map = [];

        foreach ($data as $row) {
            $map[(int)$row->month] = $row;
        }

        $months = [
            'Jan', 'Feb', 'Mar', 'Apr',
            'Mei', 'Jun', 'Jul', 'Aug',
            'Sep', 'Okt', 'Nov', 'Des',
        ];

        $sales = [];
        $profit = [];

        for ($i = 1; $i <= 12; $i++) {
            $sales[] = $map[$i]->sales ?? 0;
            $profit[] = $map[$i]->profit ?? 0;
        }

        return [
            'year' => (int) $year,
            'months' => $months,
            'net_sales_after_tax' => $sales,
            'net_profit' => $profit,
        ];
    }
    public function getYearlySales()
    {
        $query = SaleTransaction::query();

        $locationIds = auth()->user()
            ->entity
            ->locations()
            ->pluck('id')
            ->toArray();

        $query->whereIn('location_id', $locationIds);
        $query->where('status', 'ok');

        $selectAll = request('select_all_location') == 'true';

        $locs = array_map('intval', (array) request('locs', []));
        $excludeLocs = array_map('intval', (array) request('exclude_locs', []));

        if ($selectAll && count($excludeLocs) > 0) {
            $query->whereNotIn('location_id', $excludeLocs);
        } elseif (!$selectAll && count($locs) > 0) {
            $query->whereIn('location_id', $locs);
        }

        $startYear = request('start_year', now()->year - 1);
        $endYear   = request('end_year', now()->year);

        $query->whereBetween(
            DB::raw('YEAR(local_sales_at)'),
            [$startYear, $endYear]
        );

        $raw = $query
            ->selectRaw('YEAR(local_sales_at) as year')
            ->selectRaw('SUM(net_sales_after_tax) as sales')
            ->selectRaw('SUM(net_profit) as profit')
            ->groupByRaw('YEAR(local_sales_at)')
            ->orderBy('year')
            ->get()
            ->keyBy('year');

        $years = [];
        $sales = [];
        $profit = [];

        for ($y = $startYear; $y <= $endYear; $y++) {
            $years[] = $y;

            $sales[] = isset($raw[$y])
                ? (float) $raw[$y]->sales
                : 0;

            $profit[] = isset($raw[$y])
                ? (float) $raw[$y]->profit
                : 0;
        }

        return [
            'years' => $years,
            'net_sales_after_tax' => $sales,
            'net_profit' => $profit,
        ];
    }
}