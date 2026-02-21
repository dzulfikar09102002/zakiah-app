<?php

namespace App\Helpers\Data\CustomerOrder;

class Adjustment
{
    private int $quantity, $amount, $price;
    private ?int $promoId;
    private bool $freeOfCharge, $isPercentage;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
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
     * Get the value of amount
     */ 
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set the value of amount
     *
     * @return  self
     */ 
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    public function getTotalAmount()
    {
        if ($this->getFreeOfCharge()) {
            return $this->getPrice() * $this->getQuantity();
        }

        return $this->calculateAmount() * $this->getQuantity();
    }

    /**
     * Get the value of freeOfCharge
     */ 
    public function getFreeOfCharge()
    {
        return $this->freeOfCharge;
    }

    /**
     * Set the value of freeOfCharge
     *
     * @return  self
     */ 
    public function setFreeOfCharge($freeOfCharge)
    {
        $this->freeOfCharge = $freeOfCharge;

        return $this;
    }

    /**
     * Get the value of isPercentage
     */ 
    public function getIsPercentage()
    {
        return $this->isPercentage;
    }

    /**
     * Set the value of isPercentage
     *
     * @return  self
     */ 
    public function setIsPercentage($isPercentage)
    {
        $this->isPercentage = $isPercentage;

        return $this;
    }

    /**
     * Get the value of price
     */ 
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set the value of price
     *
     * @return  self
     */ 
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    public function getDiscountAmount(): int
    {
        $amount = $this->calculateAmount();
        if ($amount > 0) {
            return 0;
        }

        return abs($amount);
    }

    public function getSurchargeAmount(): int
    {
        $amount = $this->calculateAmount();
        if ($amount < 0) {
            return 0;
        }

        return $amount;
    }

    private function calculateAmount(): int
    {
        $amount = $this->getAmount();
        if ($this->getIsPercentage()) {
            $amount = $this->getPrice() * $this->getAmount() / 100;
        }

        if ($amount < 0 && $this->getPrice() < abs($amount)) {
            $amount = $this->getPrice() * -1;
        }

        return $amount;
    }

    /**
     * Get the value of promoId
     */ 
    public function getPromoId(): ?int
    {
        return $this->promoId;
    }

    /**
     * Set the value of promoId
     *
     * @return  self
     */ 
    public function setPromoId(?int $promoId)
    {
        $this->promoId = $promoId;

        return $this;
    }

    public function toArray(): array
    {
        return [
            "quantity" => $this->getQuantity(),
            "amount" => $this->getAmount(),
            "price" => $this->getPrice(),
            "promo_id" => $this->getPromoId(),
            "free_of_charge" => $this->getFreeOfCharge(),
            "is_percentage" => $this->getIsPercentage(),
            "discountAmount" => $this->getDiscountAmount(),
            "surchargeAmount" => $this->getSurchargeAmount(),
        ];
    }
}
