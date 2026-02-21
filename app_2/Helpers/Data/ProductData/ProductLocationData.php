<?php

namespace App\Helpers\Data\ProductData;

class ProductLocationData
{
    private int $locationId;
    private bool $posFavourite, $availableStockPos;

    /**
     * Create a new class instance.
     */
    public function __construct(int $locationId, bool $posFavourite = false, bool $availableStockPos = false)
    {
        $this->locationId = $locationId;
        $this->posFavourite = $posFavourite;
        $this->availableStockPos = $availableStockPos;
    }

    /**
     * Get the value of locationId
     */ 
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * Get the value of posFavourite
     */ 
    public function getPosFavourite()
    {
        return $this->posFavourite;
    }

    /**
     * Get the value of availableStockPos
     */ 
    public function getAvailableStockPos()
    {
        return $this->availableStockPos;
    }
}
