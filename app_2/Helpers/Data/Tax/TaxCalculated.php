<?php

namespace App\Helpers\Data\Tax;

class TaxCalculated
{
    private int $price, $taxAmount, $priceBeforeTax;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
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

    /**
     * Get the value of taxAmount
     */ 
    public function getTaxAmount()
    {
        return $this->taxAmount;
    }

    /**
     * Set the value of taxAmount
     *
     * @return  self
     */ 
    public function setTaxAmount($taxAmount)
    {
        $this->taxAmount = $taxAmount;

        return $this;
    }

    /**
     * Get the value of priceBeforeTax
     */ 
    public function getPriceBeforeTax()
    {
        return $this->priceBeforeTax;
    }

    /**
     * Set the value of priceBeforeTax
     *
     * @return  self
     */ 
    public function setPriceBeforeTax($priceBeforeTax)
    {
        $this->priceBeforeTax = $priceBeforeTax;

        return $this;
    }
}
