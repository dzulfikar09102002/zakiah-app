<?php

namespace App\Helpers\Queries;

use App\Models\SaleTransaction;
use Illuminate\Database\Eloquent\Builder;

class ReportSalesQuery extends ReportSaleBaseQuery
{
    protected function generateBody(): array
    {
        $bodies = array();
        foreach ($this->getData() as $data) {
            array_push($bodies, [
                'code' => $data['sales_no'],
                'receipt_no' => $data['receipt_no'],
                'location_name' => $data['location_name'],
                'local_sales_at' => $data['local_sales_at'],
                'gross_sales' => $data['gross_sales'],
                'discount_amount_before_tax' => $data['discount_amount_before_tax'],
                'surcharge_amount_before_tax' => $data['surcharge_amount_before_tax'],
                'net_sales' => $data['net_sales'],
                'tax_amount' => $data['tax_amount'],
                'net_sales_after_tax' => $data['net_sales_after_tax'],
                'gross_profit' => $data['gross_profit'],
                'net_profit' => $data['net_profit'],
                'cashier_id' => $data['cashier_id'],
                'cashier_first_name' => $data['cashier_first_name'],
                'cashier_last_name' => $data['cashier_last_name'],
                'employee_sales_id' => $data['employee_sales_id'],
                'employee_sales_first_name' => $data['employee_sales_first_name'],
                'employee_sales_last_name' => $data['employee_sales_last_name'],
                'customer_id' => $data['customer_id'],
                'customer_first_name' => $data['customer_first_name'],
                '                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   ' => $data['customer_last_name'],
            ]);
        }

        return $bodies;
    }

    private function baseQuery()
    {
        return $this->buildQuery(SaleTransaction::where('status', 'ok'));
    }

    private function buildQuery($query) {
        $this->buildWhereLocation($query);
        $this->buildWhereSalesTime($query);
        $this->buildWhereDiscounted($query);
        $this->buildWhereStatus($query);

        return $query;
    }

    protected function buildTotal(): int
    {
        return $this->baseQuery()->count('id');
    }

    private function getData()
    {
        $query = $this->baseQuery()
            ->select(
                'sales_no', 'receipt_no', 'location_name', 'local_sales_at', 'gross_sales', 'surcharge_amount_before_tax',
                'gross_profit', 'net_profit',
                'cashier_id', 'cashier_first_name', 'cashier_last_name',
                'employee_sales_id', 'employee_sales_first_name', 'employee_sales_last_name',
                'customer_id', 'customer_first_name', 'customer_last_name',
                'net_sales', 'tax_amount', 'net_sales_after_tax'
            )
            ->selectRaw('discount_amount_before_tax + promo_amount_before_tax as discount_amount_before_tax');

       return $this->queryLimitOffset($query)->get();
    }

    protected function buildWhereDiscounted($query)
    {
        if ($this->discounted == 'true') {
            $query->where(function (Builder $query) {
                $query->orWhere('discount_amount_before_tax', '>', 0)
                    ->orWhere('promo_amount_before_tax', '>', 0);
            });
        }
        else if ($this->discounted == 'false') {
            $query->where('discount_amount_before_tax', 0)->where('promo_amount_before_tax', 0);
        }
    }
}
