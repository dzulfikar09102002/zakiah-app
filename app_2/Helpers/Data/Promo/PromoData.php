<?php

namespace App\Helpers\Data\Promo;

class PromoData
{
    private int $promoId, $promoRewadId, $amount, $quantity;
    private int $appliedPromoAmount, $promoRewardAmount;
    private string $promoName, $promoRewardTemplate;
    private bool $promoRewardPercentage;
    private ?int $promoRewardMaximumAmount, $productId, $productCategoryId;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the value of promoId
     */ 
    public function getPromoId()
    {
        return $this->promoId;
    }

    /**
     * Set the value of promoId
     *
     * @return  self
     */ 
    public function setPromoId($promoId)
    {
        $this->promoId = $promoId;

        return $this;
    }

    /**
     * Get the value of promoRewadId
     */ 
    public function getPromoRewadId()
    {
        return $this->promoRewadId;
    }

    /**
     * Set the value of promoRewadId
     *
     * @return  self
     */ 
    public function setPromoRewadId($promoRewadId)
    {
        $this->promoRewadId = $promoRewadId;

        return $this;
    }

    /**
     * Get the value of amount
     */ 
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set the value of amount
     *
     * @param int $amount
     * @return  self
     */ 
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get the value of appliedPromoAmount
     */ 
    public function getAppliedPromoAmount()
    {
        return $this->appliedPromoAmount;
    }

    /**
     * Set the value of appliedPromoAmount
     *
     * @param int $appliedPromoAmount
     * @return  self
     */ 
    public function setAppliedPromoAmount($appliedPromoAmount)
    {
        $this->appliedPromoAmount = $appliedPromoAmount;

        return $this;
    }

    /**
     * Get the value of promoRewardPercentage
     */ 
    public function getPromoRewardPercentage()
    {
        return $this->promoRewardPercentage;
    }

    /**
     * Set the value of promoRewardPercentage
     *
     * @param bool $promoRewardPercentage
     * @return  self
     */ 
    public function setPromoRewardPercentage($promoRewardPercentage)
    {
        $this->promoRewardPercentage = $promoRewardPercentage;

        return $this;
    }

    /**
     * Get the value of promoRewardAmount
     */ 
    public function getPromoRewardAmount()
    {
        return $this->promoRewardAmount;
    }

    /**
     * Set the value of promoRewardAmount
     *
     * @param int $promoRewardAmount
     * @return  self
     */ 
    public function setPromoRewardAmount($promoRewardAmount)
    {
        $this->promoRewardAmount = $promoRewardAmount;

        return $this;
    }

    /**
     * Get the value of promoRewardMaximumAmount
     */ 
    public function getPromoRewardMaximumAmount()
    {
        return $this->promoRewardMaximumAmount;
    }

    /**
     * Set the value of promoRewardMaximumAmount
     *
     * @param ?int $promoRewardMaximumAmount
     * @return  self
     */ 
    public function setPromoRewardMaximumAmount($promoRewardMaximumAmount)
    {
        $this->promoRewardMaximumAmount = $promoRewardMaximumAmount;

        return $this;
    }

    /**
     * Get the value of promoName
     */ 
    public function getPromoName()
    {
        return $this->promoName;
    }

    /**
     * Set the value of promoName
     *
     * @return  self
     */ 
    public function setPromoName($promoName)
    {
        $this->promoName = $promoName;

        return $this;
    }

    /**
     * Get the value of promoRewardTemplate
     */ 
    public function getPromoRewardTemplate()
    {
        return $this->promoRewardTemplate;
    }

    /**
     * Set the value of promoRewardTemplate
     *
     * @param string $promoRewardTemplate
     * @return  self
     */ 
    public function setPromoRewardTemplate($promoRewardTemplate)
    {
        $this->promoRewardTemplate = $promoRewardTemplate;

        return $this;
    }

    /**
     * Get the value of quantity
     */ 
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set the value of quantity
     *
     * @return  self
     */ 
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get the value of productId
     */ 
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * Set the value of productId
     *
     * @return  self
     */ 
    public function setProductId($productId)
    {
        $this->productId = $productId;

        return $this;
    }

    /**
     * Get the value of productCategoryId
     */ 
    public function getProductCategoryId()
    {
        return $this->productCategoryId;
    }

    /**
     * Set the value of productCategoryId
     *
     * @return  self
     */ 
    public function setProductCategoryId($productCategoryId)
    {
        $this->productCategoryId = $productCategoryId;

        return $this;
    }

    public function toArray(): array
    {
        return [
            "promoId" => $this->promoId,
            "promoRewadId" => $this->promoRewadId,
            "amount" => $this->amount,
            "quantity" => $this->quantity,
            "appliedPromoAmount" => $this->appliedPromoAmount,
            "promoRewardAmount" => $this->promoRewardAmount,
            "promoName" => $this->promoName,
        ];
    }
}
