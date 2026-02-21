<?php

namespace App\Helpers\Data\ProductData;

use App\Enums\TaxSettingEnum;

class ProductSellPriceData
{
    private ?int $locationId, $orderTypeId, $productUnitId, $taxId, $sellPrice;
    private ?TaxSettingEnum $taxSetting;
    
    public function __construct(int $locationId, ?int $orderTypeId, ?int $productUnitId, ?int $taxId, ?TaxSettingEnum $taxSetting, int $sellPrice)
    {
        $this->locationId = $locationId;
        $this->orderTypeId = $orderTypeId;
        $this->productUnitId = $productUnitId;
        $this->taxId = $taxId;
        $this->taxSetting = $taxSetting;
        $this->sellPrice = $sellPrice;
    }

    /**
     * Get the value of locationId
     */ 
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * Get the value of orderTypeId
     */ 
    public function getOrderTypeId()
    {
        return $this->orderTypeId;
    }

    /**
     * Get the value of sellPrice
     */ 
    public function getSellPrice()
    {
        return $this->sellPrice;
    }

    /**
     * Get the value of productUnitId
     */ 
    public function getProductUnitId()
    {
        return $this->productUnitId;
    }

    /**
     * Get the value of taxId
     */ 
    public function getTaxId()
    {
        return $this->taxId;
    }

    /**
     * Get the value of taxSetting
     */ 
    public function getTaxSetting()
    {
        return $this->taxSetting;
    }
}
