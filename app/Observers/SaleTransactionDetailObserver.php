<?php

namespace App\Observers;

use App\Models\EmployeeSalesSummary;
use App\Models\SaleTransactionDetail;
use Carbon\Carbon;

class SaleTransactionDetailObserver
{
    /**
     * Handle the SaleTransactionDetail "created" event.
     */
    public function created(SaleTransactionDetail $saleTransactionDetail): void
    {
        //
        $saleTransaction = $saleTransactionDetail->saleTransaction()->first();
        $local_sales_date = (new Carbon($saleTransaction->local_sales_at))->format('Y-m-d');

        $summary = EmployeeSalesSummary::firstOrNew([
            'location_id' => $saleTransaction->location_id,
            'employee_sales_id' => $saleTransaction->employee_sales_id,
            'local_sales_date' => $local_sales_date,
        ]);

        if ($summary->id == null) {
            $employeeSales = $saleTransaction->employeeSales()->first();
            $location = $saleTransaction->location()->first();

            $summary->location_name = $location->name;
            $summary->employee_sales_name = $employeeSales->first_name . ' ' . $employeeSales->last_name;

            $summary->sales_amount = 0;
            $summary->refund_amount = 0;
            $summary->net_sales_amount = 0;

            $summary->sales_count = 0;
            $summary->refund_count = 0;
            $summary->net_count = 0;

            $summary->sales_quantity = 0;
            $summary->refund_quantity = 0;
            $summary->net_quantity = 0;
        }

        $summary->sales_amount += $saleTransactionDetail->total_line_amount;
        $summary->net_sales_amount = $summary->sales_amount - $summary->refund_amount;
        
        $summary->sales_count += 1;
        $summary->net_count = $summary->sales_count - $summary->refund_count;

        $summary->sales_quantity += $saleTransactionDetail->quantity;
        $summary->net_quantity = $summary->sales_quantity - $summary->refund_quantity;

        $summary->save();
    }

    /**
     * Handle the SaleTransactionDetail "updated" event.
     */
    public function updated(SaleTransactionDetail $saleTransactionDetail): void
    {
        //
    }

    /**
     * Handle the SaleTransactionDetail "deleted" event.
     */
    public function deleted(SaleTransactionDetail $saleTransactionDetail): void
    {
        //
    }

    /**
     * Handle the SaleTransactionDetail "restored" event.
     */
    public function restored(SaleTransactionDetail $saleTransactionDetail): void
    {
        //
    }

    /**
     * Handle the SaleTransactionDetail "force deleted" event.
     */
    public function forceDeleted(SaleTransactionDetail $saleTransactionDetail): void
    {
        //
    }
}
