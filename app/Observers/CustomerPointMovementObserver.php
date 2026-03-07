<?php

namespace App\Observers;

use App\Models\CustomerPoint;
use App\Models\CustomerPointMovement;

class CustomerPointMovementObserver
{
    /**
     * Handle the CustomerPointMovement "created" event.
     */
    public function created(CustomerPointMovement $customerPointMovement): void
    {
        //
        $customerPoint = CustomerPoint::where('id', $customerPointMovement->customer_point_id)->lockForUpdate()->first();

        $customerPoint->total_point = $customerPoint->total_point + $customerPointMovement->point;
        $customerPoint->save();
    }

    /**
     * Handle the CustomerPointMovement "updated" event.
     */
    public function updated(CustomerPointMovement $customerPointMovement): void
    {
        //
    }

    /**
     * Handle the CustomerPointMovement "deleted" event.
     */
    public function deleted(CustomerPointMovement $customerPointMovement): void
    {
        //
        $customerPoint = CustomerPoint::where('id', $customerPointMovement->customer_point_id)->lockForUpdate()->first();
        $customerPoint->total_point = $customerPoint->total_point - $customerPointMovement->point;
        $customerPoint->save();
    }

    /**
     * Handle the CustomerPointMovement "restored" event.
     */
    public function restored(CustomerPointMovement $customerPointMovement): void
    {
        //
    }

    /**
     * Handle the CustomerPointMovement "force deleted" event.
     */
    public function forceDeleted(CustomerPointMovement $customerPointMovement): void
    {
        //
    }
}
