<?php

namespace App\Helpers\Services\CustomerPoint\Helpers;

use App\Models\Customer;
use App\Models\CustomerPoint;
use App\Models\Loyalty;

class CustomerPointValidateHelper
{
    /**
     * Create a new class instance.
     */
    public static function validate(?Loyalty $loyalty, ?Customer $customer, ?CustomerPoint $customerPoint): bool
    {
        //
        if ($customer == null || $customerPoint == null || $loyalty == null) {
            return false;
        }

        # validate location
        # validate channel
        # validate rule customer category

        return true;
    }

    public static function findOrCreateCustomerPoint(?Customer $customer): ?CustomerPoint
    {
        if ($customer == null) {
            return null;
        }

        if ($customer->customerPoint()->first() == null) {
            return $customer->customerPoint()->create();
        }

        return $customer->customerPoint()->first();
    }
}
