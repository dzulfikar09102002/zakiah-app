<?php

namespace App\Helpers\Queries;

use App\Models\SaleTransactionDetail;

class ReportSalesByLocationQuery extends ReportSaleBaseQuery
{
    protected function generateBody(): array
    {
        $bodies = array();
        foreach ($this->getData() as $data) {
            array_push($bodies, [
                'location_name' => $data['location_name'],
                'quantity' => $data['quantity'],
                'cancelled_quantity' => $data['cancelled_quantity'],
                'cost_of_goods_sold' => $data['cost_of_goods_sold'],
                'gross_sales' => $data['gross_sales'],
                'gross_refund' => $data['gross_refund'],
                'discount_amount' => $data['discount_amount'] + $data['promo_amount'] + $data['prorate_promo_amount'] + $data['prorate_discount_amount'],
                'total_amount' => $data['total_amount'],
                'gross_profit' => $data['gross_profit'],
                'net_profit' => $data['net_profit'],
            ]);
        }

        return $bodies;
    }

    private function baseQuery()
    {
        return $this->buildQuery(SaleTransactionDetail::where('status', 'ok'));
    }

    private function buildQuery($query) {
        $this->buildWhereLocation($query);
        $this->buildWhereSalesTime($query);
        $this->buildWhereDiscounted($query);
        $this->buildWhereStatus($query);

        return $query->selectRaw('location_name')->groupBy('location_name');
    }

    protected function buildTotal(): int
    {
        // fromSub
        return SaleTransactionDetail::fromSub(function ($query) {
            $this->buildQuery($query->from('sale_transaction_details'));
        }, 'a')->count('location_name');
    }

    private function getData()
    {
        $query = $this->baseQuery()
            ->selectRaw('sum(cost_of_goods_sold * quantity) as cost_of_goods_sold')
            ->selectRaw('sum((sell_price - sell_price_tax_amount) * quantity) as gross_sales')
            ->selectRaw('sum((cast(sell_price as signed) - cast(sell_price_tax_amount as signed) - cast(cost_of_goods_sold as signed)) * cast(quantity as signed)) as gross_profit')
            ->selectRaw('sum((sell_price - sell_price_tax_amount) * cancelled_quantity) as gross_refund')
            ->selectRaw('sum((discount_amount - discount_amount_tax_amount) * quantity) as discount_amount')
            ->selectRaw('sum((promo_amount - promo_amount_tax_amount) * quantity) as promo_amount')
            ->selectRaw('sum((prorate_promo_amount - prorate_promo_amount_tax_amount) * 1) as prorate_promo_amount')
            ->selectRaw('sum((prorate_discount_amount - prorate_discount_amount_tax_amount) * 1) as prorate_discount_amount')
            ->selectRaw('sum(
                        (cast(sell_price as signed) - cast(sell_price_tax_amount as signed) 
                        - cast(cost_of_goods_sold as signed) 
                        - cast(discount_amount as signed)  - cast(discount_amount_tax_amount as signed) 
                        - cast(promo_amount as signed) - cast(promo_amount_tax_amount as signed)
                        + cast(surcharge_amount as signed) - cast(surcharge_amount_tax_amount as signed)) * cast(quantity as signed)
                        + cast(prorate_surcharge_amount as signed) - cast(prorate_surcharge_amount_tax_amount as signed)
                        - cast(prorate_discount_amount as signed) - cast(prorate_discount_amount_tax_amount as signed) 
                        - cast(prorate_promo_amount as signed) - cast(prorate_promo_amount_tax_amount as signed)) as net_profit')
            ->selectRaw('sum(quantity) as quantity')
            ->selectRaw('sum(cancelled_quantity) as cancelled_quantity')
            ->selectRaw('sum(total_amount - prorate_promo_amount - prorate_promo_amount_tax_amount - prorate_discount_amount - prorate_discount_amount_tax_amount) as total_amount');

       return $this->queryLimitOffset($query)->get();
    }

    protected function buildWhereDiscounted($query)
    {
        if ($this->discounted == 'true') {
            $query->whereRaw('(discount_amount > ? or promo_amount > ? or prorate_promo_amount > ? or prorate_discount_amount > ?)', [0, 0, 0, 0]);
        }
        else if ($this->discounted == 'false') {
            $query->where('discount_amount', 0)->where('prorate_promo_amount', 0)
            ->where('promo_amount', 0)->where('prorate_discount_amount', 0);
        }
    }
}
