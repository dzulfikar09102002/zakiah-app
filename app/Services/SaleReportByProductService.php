<?php

namespace App\Services;

use App\Models\Location;
use App\Models\SaleTransaction;
use App\Models\SaleTransactionDetail;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SaleReportByProductService
{
    public function getLocationOptions()
    {
        return (new LocationService)->getLocations()->get()->map(fn (Location $location) => [
            'label' => Str::title(Str::lower($location->name)),
            'value' => $location->id,
        ]);
    }

    public function getSaleReportByProducts()
    {
        $startAt = request('start_at')
            ? Carbon::parse(request('start_at'))->startOfDay()
            : Carbon::now()->subDays(7)->startOfDay();

        $endAt = request('end_at')
            ? Carbon::parse(request('end_at'))->endOfDay()
            : Carbon::now()->endOfDay();

        $statuses = request('statuses', ['ok']);

        $query = SaleTransactionDetail::query();

        $locationIds = auth()->user()
            ->entity
            ->locations()
            ->pluck('id')
            ->toArray();

        $query->whereIn('location_id', $locationIds);

        $selectAll = request('select_all_location') == '1';
        $locs = array_map('intval', (array) request('locs', []));
        $excludeLocs = array_map('intval', (array) request('exclude_locs', []));

        if ($selectAll && count($excludeLocs) > 0) {
            $query->whereNotIn('location_id', $excludeLocs);
        } elseif (!$selectAll && count($locs) > 0) {
            $query->whereIn('location_id', $locs);
        }

        $query->whereBetween('local_sales_at', [
            $startAt,
            $endAt
        ]);
        $query->whereIn('status', $statuses);

        if (request('discount') === 'available') {
            $query->where(function ($q) {
                $q->where('discount_amount', '>', 0)
                    ->orWhere('promo_amount', '>', 0)
                    ->orWhere('prorate_discount_amount', '>', 0)
                    ->orWhere('prorate_promo_amount', '>', 0);
            });
        }

        if (request('discount') === 'none') {
            $query->where('discount_amount', 0)
                ->where('promo_amount', 0)
                ->where('prorate_discount_amount', 0)
                ->where('prorate_promo_amount', 0);
        }

        if ($search = request('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_sku', 'like', "%{$search}%");
            });
        }

        return $query
            ->selectRaw('
                product_id,
                product_name,
                product_sku,
                IFNULL(product_category_name, "-") as product_category_name,
                product_description,
                sell_price,
                cost_of_goods_sold
            ')
            ->selectRaw('SUM(quantity) as quantity')
            ->selectRaw('SUM(cancelled_quantity) as cancelled_quantity')

            ->selectRaw('
                SUM(
                    (sell_price - sell_price_tax_amount)
                    * quantity
                ) as gross_sales
            ')

            ->selectRaw('
                SUM(
                    (sell_price - sell_price_tax_amount)
                    * cancelled_quantity
                ) as gross_refund
            ')

            ->selectRaw('
                SUM(
                    (discount_amount - discount_amount_tax_amount)
                    * quantity
                ) as discount_amount
            ')

            ->selectRaw('
                SUM(
                    (promo_amount - promo_amount_tax_amount)
                    * quantity
                ) as promo_amount
            ')

            ->selectRaw('
                SUM(
                    prorate_promo_amount
                    - prorate_promo_amount_tax_amount
                ) as prorate_promo_amount
            ')

            ->selectRaw('
                SUM(
                    prorate_discount_amount
                    - prorate_discount_amount_tax_amount
                ) as prorate_discount_amount
            ')

            ->selectRaw('
                SUM(
                    total_amount
                    - prorate_promo_amount
                    - prorate_promo_amount_tax_amount
                    - prorate_discount_amount
                    - prorate_discount_amount_tax_amount
                ) as total_amount
            ')

            ->selectRaw('
                SUM(
                    (
                        CAST(sell_price AS SIGNED)
                        - CAST(sell_price_tax_amount AS SIGNED)
                        - CAST(cost_of_goods_sold AS SIGNED)
                        - CAST(discount_amount AS SIGNED)
                        - CAST(discount_amount_tax_amount AS SIGNED)
                        - CAST(promo_amount AS SIGNED)
                        - CAST(promo_amount_tax_amount AS SIGNED)
                        + CAST(surcharge_amount AS SIGNED)
                        - CAST(surcharge_amount_tax_amount AS SIGNED)
                    ) * CAST(quantity AS SIGNED)

                    + CAST(prorate_surcharge_amount AS SIGNED)
                    - CAST(prorate_surcharge_amount_tax_amount AS SIGNED)

                    - CAST(prorate_discount_amount AS SIGNED)
                    - CAST(prorate_discount_amount_tax_amount AS SIGNED)

                    - CAST(prorate_promo_amount AS SIGNED)
                    - CAST(prorate_promo_amount_tax_amount AS SIGNED)
                ) as profit
            ')
            ->groupBy(
                'product_id',
                'product_name',
                'product_sku',
                'product_category_name',
                'product_description',
                'sell_price',
                'cost_of_goods_sold'
            )
            ->paginate(request('per_page', 10))
            ->through(fn($row) => [
                'product_name' => $row->product_name,
                'product_sku' => $row->product_sku,
                'category' => $row->product_category_name,
                'description' => $row->product_description,
                'quantity' => (int) $row->quantity,
                'sell_price' => (int) $row->sell_price,
                'cost_of_goods_sold' => (int) $row->cost_of_goods_sold,

                'gross_sales' => (int) $row->gross_sales,

                'discount' =>
                    (int) $row->discount_amount +
                    (int) $row->promo_amount +
                    (int) $row->prorate_discount_amount +
                    (int) $row->prorate_promo_amount,

                'total' => (int) $row->total_amount,

                'profit' => (int) $row->profit,
            ])
            ->withQueryString();
    }
}
