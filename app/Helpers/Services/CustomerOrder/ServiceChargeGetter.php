<?php

namespace App\Helpers\Services\CustomerOrder;

use App\Models\Location;

class ServiceChargeGetter
{

    private int $serviceChargeRate;
    private bool $serviceChargeIncludeTax;

    /**
     * Create a new class instance.
     */
    public function __construct(Location $location)
    {
        //
        $this->serviceChargeRate = 0;
        $this->serviceChargeIncludeTax = true;
    }

    /**
     * Get the value of serviceChargeRate
     */ 
    public function getServiceChargeRate()
    {
        return $this->serviceChargeRate;
    }

    /**
     * Get the value of serviceChargeIncludeTax
     */ 
    public function getServiceChargeIncludeTax()
    {
        return $this->serviceChargeIncludeTax;
    }
}
