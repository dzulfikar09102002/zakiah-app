<?php

namespace App\Helpers\Data\ProductData;

class ProductLocationStockData
{
    private int $locationId, $stock;
    private ?int $productUnitId;

    /**
     * Create a new class instance.
     */
    public function __construct(int $locationId, $stock = 0, ?int $productUnitId = null)
    {
        $this->locationId = $locationId;
        $this->productUnitId = $productUnitId;
        $this->stock = $stock;
    }

    /**
     * Get the value of locationId
     */ 
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * Get the value of stock
     */ 
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * Get the value of productUnitId
     */ 
    public function getProductUnitId()
    {
        return $this->productUnitId;
    }
}
