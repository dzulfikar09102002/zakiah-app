<?php

namespace App\Helpers\Data\Promo;

use App\Models\Promo;

class PromoApplicable
{
    private array $promoIds;
    private array $promoTotalOrder;
    private array $promoProduct;
    private array $promoProductCategory;

    public function __construct()
    {
        //
    }

    /**
     * Get the value of promoIds
     */ 
    public function getPromoIds()
    {
        return $this->promoIds;
    }

    /**
     * Set the value of promoIds
     *
     * @return  self
     */ 
    public function setPromoIds($promoIds)
    {
        $this->promoIds = $promoIds;

        return $this;
    }

    /**
     * Get the value of promoTotalOrder
     * @return Promo[]
     */ 
    public function getPromoTotalOrder()
    {
        return $this->promoTotalOrder;
    }

    /**
     * Set the value of promoTotalOrder
     * 
     * @param Promo[] $promoTotalOrder
     *
     * @return  self
     */ 
    public function setPromoTotalOrder($promoTotalOrder)
    {
        $this->promoTotalOrder = $promoTotalOrder;

        return $this;
    }

    /**
     * Get the value of promoProduct
     * @return Promo[]
     * 
     */ 
    public function getPromoProduct()
    {
        return $this->promoProduct;
    }

    /**
     * Set the value of promoProduct
     * 
     * @param Promo[] $promoProduct
     *
     * @return  self
     */ 
    public function setPromoProduct($promoProduct)
    {
        $this->promoProduct = $promoProduct;

        return $this;
    }

    /**
     * Get the value of promoProductCategory
     * @return Promo[]
     * 
     */ 
    public function getPromoProductCategory()
    {
        return $this->promoProductCategory;
    }

    /**
     * Set the value of promoProductCategory
     * 
     * @param Promo[] $promoProductCategory
     *
     * @return  self
     */ 
    public function setPromoProductCategory($promoProductCategory)
    {
        $this->promoProductCategory = $promoProductCategory;

        return $this;
    }
}
