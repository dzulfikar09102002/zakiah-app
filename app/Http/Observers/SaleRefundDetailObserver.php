<?php

namespace App\Observers;

use App\Models\EmployeeSalesSummary;
use App\Models\SaleRefundDetail;
use Carbon\Carbon;

class SaleRefundDetailObserver
{
    /**
     * Handle the SaleRefundDetail "created" event.
     */
    public function created(SaleRefundDetail $saleRefundDetail): void
    {
        //
        $saleTransaction = $saleRefundDetail->saleTransaction()->get();
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

        $summary->refund_amount += $saleRefundDetail->total_line_amount;
        $summary->net_sales_amount = $summary->sales_amount - $summary->refund_amount;
        
        $summary->refund_count += 1;
        $summary->net_count = $summary->sales_count - $summary->refund_count;

        $summary->refund_quantity += $saleRefundDetail->quantity;
        $summary->net_quantity = $summary->sales_quantity - $summary->refund_quantity;

        $summary->save();
    }

    /**
     * Handle the SaleRefundDetail "updated" event.
     */
    public function updated(SaleRefundDetail $saleRefundDetail): void
    {
        //
    }

    /**
     * Handle the SaleRefundDetail "deleted" event.
     */
    public function deleted(SaleRefundDetail $saleRefundDetail): void
    {
        //
    }

    /**
     * Handle the SaleRefundDetail "restored" event.
     */
    public function restored(SaleRefundDetail $saleRefundDetail): void
    {
        //
    }

    /**
     * Handle the SaleRefundDetail "force deleted" event.
     */
    public function forceDeleted(SaleRefundDetail $saleRefundDetail): void
    {
        //
    }
}
