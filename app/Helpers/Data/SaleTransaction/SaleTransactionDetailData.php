<?php

namespace App\Helpers\Data\SaleTransaction;

use App\Models\SaleTransactionDetail;

class SaleTransactionDetailData
{
    private int $grossSales, $discountAmountBeforeTax, $surchargeAmountBeforeTax, $promoAmountBeforeTax, $freeOfChargeAmountBeforeTax;
    private int $serviceChargeBeforeTax, $taxAmount, $roundingAmount, $roundingTaxAmount, $roundingServiceChargeAmount;
    private int $discountAmount, $surchargeAmount, $promoAmount, $subtotal, $grossProfit, $netProfit;
    private array $productIds, $productCategoryIds;
    private array $saleDetailTransactions;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
        $this->grossSales = 0;
        $this->grossProfit = 0;
        $this->netProfit = 0;
        $this->discountAmountBeforeTax = 0;
        $this->surchargeAmountBeforeTax = 0;
        $this->promoAmountBeforeTax = 0;
        $this->freeOfChargeAmountBeforeTax = 0;

        $this->serviceChargeBeforeTax = 0;
        $this->taxAmount = 0;
        $this->roundingAmount = 0;
        $this->roundingTaxAmount = 0;
        $this->roundingServiceChargeAmount = 0;

        $this->discountAmount = 0;
        $this->surchargeAmount = 0;
        $this->promoAmount =0 ;
        $this->subtotal = 0;

        $this->saleDetailTransactions = array();
        $this->productIds = array();
        $this->productCategoryIds = array();
    }

    /**
     * Get the value of grossSales
     */ 
    public function getGrossSales()
    {
        return $this->grossSales;
    }

    /**
     * Set the value of grossSales
     *
     * @return  self
     */ 
    public function setGrossSales($grossSales)
    {
        $this->grossSales = $grossSales;

        return $this;
    }

    /**
     * sum the value of grossSales
     *
     * @return  self
     */ 
    public function addGrossSales(SaleTransactionDetail $saleTransactionDetail, ?int $quantity = null)
    {
        if ($quantity == null) {
            $quantity = $saleTransactionDetail->quantity;
        }

        $amount = $saleTransactionDetail->sell_price - $saleTransactionDetail->sell_price_tax_amount;
        $this->grossSales += $amount * $quantity;

        return $this;
    }

    /**
     * Get the value of discountAmountBeforeTax
     */ 
    public function getDiscountAmountBeforeTax()
    {
        return $this->discountAmountBeforeTax;
    }

    /**
     * Set the value of discountAmountBeforeTax
     *
     * @return  self
     */ 
    public function setDiscountAmountBeforeTax($discountAmountBeforeTax)
    {
        $this->discountAmountBeforeTax = $discountAmountBeforeTax;

        return $this;
    }

    /**
     * sum the value of discountAmountBeforeTax
     *
     * @return  self
     */ 
    public function addDiscountAmountBeforeTax(SaleTransactionDetail $saleTransactionDetail, ?int $quantity = null)
    {
        if ($quantity == null) {
            $quantity = $saleTransactionDetail->quantity;
        }

        $amount = ($saleTransactionDetail->discount_amount - $saleTransactionDetail->discount_amount_tax_amount) * $quantity;
        $amount += ($saleTransactionDetail->prorate_discount_amount - $saleTransactionDetail->prorate_discount_amount_tax_amount) * $quantity;

        $this->discountAmountBeforeTax += $amount;

        return $this;
    }

    /**
     * Get the value of surchargeAmountBeforeTax
     */ 
    public function getSurchargeAmountBeforeTax()
    {
        return $this->surchargeAmountBeforeTax;
    }

    /**
     * Set the value of surchargeAmountBeforeTax
     *
     * @return  self
     */ 
    public function setSurchargeAmountBeforeTax($surchargeAmountBeforeTax)
    {
        $this->surchargeAmountBeforeTax = $surchargeAmountBeforeTax;

        return $this;
    }

    /**
     * sum the value of surchargeAmountBeforeTax
     *
     * @return  self
     */ 
    public function addSurchargeAmountBeforeTax(SaleTransactionDetail $saleTransactionDetail, ?int $quantity = null)
    {
        if ($quantity == null) {
            $quantity = $saleTransactionDetail->quantity;
        }

        $amount = ($saleTransactionDetail->surcharge_amount - $saleTransactionDetail->surcharge_amount_tax_amount) * $quantity;
        $amount += ($saleTransactionDetail->prorate_surcharge_amount - $saleTransactionDetail->prorate_surcharge_amount_tax_amount) * $quantity;
        
        $this->surchargeAmountBeforeTax += $amount;

        return $this;
    }

    /**
     * Get the value of promoAmountBeforeTax
     */ 
    public function getPromoAmountBeforeTax()
    {
        return $this->promoAmountBeforeTax;
    }

    /**
     * Set the value of promoAmountBeforeTax
     *
     * @return  self
     */ 
    public function setPromoAmountBeforeTax($promoAmountBeforeTax)
    {
        $this->promoAmountBeforeTax = $promoAmountBeforeTax;

        return $this;
    }

    /**
     * sum the value of promoAmountBeforeTax
     *
     * @return  self
     */ 
    public function addPromoAmountBeforeTax(SaleTransactionDetail $saleTransactionDetail, ?int $quantity = null)
    {
        if ($quantity == null) {
            $quantity = $saleTransactionDetail->quantity;
        }

        $amount = ($saleTransactionDetail->promo_amount - $saleTransactionDetail->promo_amount_tax_amount) * $quantity;
        $amount += ($saleTransactionDetail->prorate_promo_amount - $saleTransactionDetail->prorate_promo_amount_tax_amount) * $quantity;
        
        $this->promoAmountBeforeTax += $amount;

        return $this;
    }

    /**
     * Get the value of freeOfChargeAmountBeforeTax
     */ 
    public function getFreeOfChargeAmountBeforeTax()
    {
        return $this->freeOfChargeAmountBeforeTax;
    }

    /**
     * Set the value of freeOfChargeAmountBeforeTax
     *
     * @return  self
     */ 
    public function setFreeOfChargeAmountBeforeTax($freeOfChargeAmountBeforeTax)
    {
        $this->freeOfChargeAmountBeforeTax = $freeOfChargeAmountBeforeTax;

        return $this;
    }

    /**
     * sum the value of freeOfChargeAmountBeforeTax
     *
     * @return  self
     */ 
    public function addFreeOfChargeAmountBeforeTax(SaleTransactionDetail $saleTransactionDetail, ?int $quantity = null)
    {
        if ($quantity == null) {
            $quantity = $saleTransactionDetail->quantity;
        }
        
        $amount = ($saleTransactionDetail->free_of_charge_amount - $saleTransactionDetail->free_of_charge_amount_tax_amount) * $quantity;
        $amount += $saleTransactionDetail->prorate_free_of_charge_amount - $saleTransactionDetail->prorate_free_of_charge_amount_tax_amount;
        
        $this->freeOfChargeAmountBeforeTax += $amount;

        return $this;
    }

    public function getNetSales()
    {
        return $this->getGrossSales() - 
            $this->getDiscountAmountBeforeTax() + 
            $this->getSurchargeAmountBeforeTax() - 
            $this->getPromoAmountBeforeTax() -
            $this->getFreeOfChargeAmountBeforeTax();
    }

    /**
     * Get the value of serviceChargeBeforeTax
     */ 
    public function getServiceChargeBeforeTax()
    {
        return $this->serviceChargeBeforeTax;
    }

    /**
     * Set the value of serviceChargeBeforeTax
     *
     * @return  self
     */ 
    public function setServiceChargeBeforeTax($serviceChargeBeforeTax)
    {
        $this->serviceChargeBeforeTax = $serviceChargeBeforeTax;

        return $this;
    }

    /**
     * sum the value of serviceChargeBeforeTax
     *
     * @return  self
     */ 
    public function addServiceChargeBeforeTax(SaleTransactionDetail $saleTransactionDetail, ?int $quantity = null)
    {
        if ($quantity == null) {
            $quantity = $saleTransactionDetail->quantity;
        }
        
        $amount = $saleTransactionDetail->service_charge - $saleTransactionDetail->service_charge_tax_amount;

        $this->serviceChargeBeforeTax += $amount * $quantity;

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
     * sum the value of taxAmount
     *
     * @return  self
     */ 
    public function addTaxAmount(SaleTransactionDetail $saleTransactionDetail, ?int $quantity = null)
    {
        if ($quantity == null) {
            $quantity = $saleTransactionDetail->quantity;
        }
        
        $amount = $saleTransactionDetail->sell_price_tax_amount -
            $saleTransactionDetail->promo_amount_tax_amount -
            $saleTransactionDetail->discount_amount_tax_amount +
            $saleTransactionDetail->surcharge_amount_tax_amount -
            $saleTransactionDetail->free_of_charge_amount_tax_amount +
            $saleTransactionDetail->service_charge_tax_amount;

        $this->taxAmount += $amount * $quantity;

        return $this;
    }

    /**
     * Get the value of roundingAmount
     */ 
    public function getRoundingAmount()
    {
        return $this->roundingAmount;
    }

    /**
     * Set the value of roundingAmount
     *
     * @return  self
     */ 
    public function setRoundingAmount($roundingAmount)
    {
        $this->roundingAmount = $roundingAmount;

        return $this;
    }

    /**
     * Get the value of roundingTaxAmount
     */ 
    public function getRoundingTaxAmount()
    {
        return $this->roundingTaxAmount;
    }

    /**
     * Set the value of roundingTaxAmount
     *
     * @return  self
     */ 
    public function setRoundingTaxAmount($roundingTaxAmount)
    {
        $this->roundingTaxAmount = $roundingTaxAmount;

        return $this;
    }

    /**
     * Get the value of roundingServiceChargeAmount
     */ 
    public function getRoundingServiceChargeAmount()
    {
        return $this->roundingServiceChargeAmount;
    }

    /**
     * Set the value of roundingServiceChargeAmount
     *
     * @return  self
     */ 
    public function setRoundingServiceChargeAmount($roundingServiceChargeAmount)
    {
        $this->roundingServiceChargeAmount = $roundingServiceChargeAmount;

        return $this;
    }

    public function getNetSalesAfterTax()
    {
        return $this->getNetSales() + 
            $this->getServiceChargeBeforeTax() + 
            $this->getTaxAmount();
    }

    /**
     * Get the value of saleDetailTransactions
     */ 
    public function getSaleDetailTransactions()
    {
        return $this->saleDetailTransactions;
    }

    /**
     * Set the value of saleDetailTransactions
     *
     * @return  self
     */ 
    public function setSaleDetailTransactions($saleDetailTransactions)
    {
        $this->saleDetailTransactions = $saleDetailTransactions;

        return $this;
    }

    public function addSaleDetailTransactions($saleDetailTransaction)
    {
        array_push($this->saleDetailTransactions, $saleDetailTransaction);

        return $this;
    }

    /**
     * Get the value of productIds
     */ 
    public function getProductIds()
    {
        return $this->productIds;
    }

    /**
     * Set the value of productIds
     *
     * @return  self
     */ 
    public function setProductIds($productIds)
    {
        $this->productIds = $productIds;

        return $this;
    }

    /**
     * Set the value of productIds
     *
     * @return  self
     */ 
    public function addProductIds($productId)
    {
        array_push($this->productIds, $productId);

        return $this;
    }

    /**
     * Get the value of productCategoryIds
     */ 
    public function getProductCategoryIds()
    {
        return $this->productCategoryIds;
    }

    /**
     * Set the value of productCategoryIds
     *
     * @return  self
     */ 
    public function setProductCategoryIds($productCategoryIds)
    {
        $this->productCategoryIds = $productCategoryIds;

        return $this;
    }

    public function addProductCategoryIds($productCategoryId)
    {
        array_push($this->productCategoryIds, $productCategoryId);

        return $this;
    }

    /**
     * Get the value of discountAmount
     */ 
    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * Set the value of discountAmount
     *
     * @return  self
     */ 
    public function setDiscountAmount($discountAmount)
    {
        $this->discountAmount = $discountAmount;

        return $this;
    }

    public function addDiscountAmount(SaleTransactionDetail $saleTransactionDetail)
    {
        $amount = $saleTransactionDetail->prorate_discount_amount;

        $this->discountAmount += $amount;

        return $this;
    }

    /**
     * Get the value of surchargeAmount
     */ 
    public function getSurchargeAmount()
    {
        return $this->surchargeAmount;
    }

    /**
     * Set the value of surchargeAmount
     *
     * @return  self
     */ 
    public function setSurchargeAmount($surchargeAmount)
    {
        $this->surchargeAmount = $surchargeAmount;

        return $this;
    }

    public function addSurchargeAmount(SaleTransactionDetail $saleTransactionDetail)
    {
        $amount = $saleTransactionDetail->prorate_surcharge_amount;
        
        $this->surchargeAmount += $amount;

        return $this;
    }

    /**
     * Get the value of promoAmount
     */ 
    public function getPromoAmount()
    {
        return $this->promoAmount;
    }

    /**
     * Set the value of promoAmount
     *
     * @return  self
     */ 
    public function setPromoAmount($promoAmount)
    {
        $this->promoAmount = $promoAmount;

        return $this;
    }

    public function addPromoAmount(SaleTransactionDetail $saleTransactionDetail)
    {
        $amount = $saleTransactionDetail->prorate_promo_amount;
        
        $this->promoAmount += $amount;

        return $this;
    }

    /**
     * Get the value of subtotal
     */ 
    public function getSubtotal()
    {
        return $this->subtotal;
    }

    /**
     * Set the value of subtotal
     *
     * @return  self
     */ 
    public function setSubtotal($subtotal)
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    public function addSubtotal(SaleTransactionDetail $saleTransactionDetail, ?int $quantity = null)
    {
        if ($quantity == null) {
            $quantity = $saleTransactionDetail->quantity;
        }

        $amount = $saleTransactionDetail->sell_price - $saleTransactionDetail->discount_amount - $saleTransactionDetail->promo_amount + $saleTransactionDetail->surcharge_amount;
        $this->subtotal += $amount * $quantity;

        return $this;
    }

    /**
     * Get the value of grossProfit
     */ 
    public function getGrossProfit()
    {
        return $this->grossProfit;
    }

    /**
     * Set the value of grossProfit
     *
     * @return  self
     */ 
    public function setGrossProfit($grossProfit)
    {
        $this->grossProfit = $grossProfit;

        return $this;
    }

    /**
     * sum the value of grossProfit
     *
     * @return  self
     */ 
    public function addGrossProfit(SaleTransactionDetail $saleTransactionDetail, ?int $quantity = null)
    {
        if ($quantity == null) {
            $quantity = $saleTransactionDetail->quantity;
        }

        $amount = $saleTransactionDetail->sell_price - $saleTransactionDetail->sell_price_tax_amount - $saleTransactionDetail->cost_of_goods_sold;
        $this->grossProfit += $amount * $quantity;

        return $this;
    }

    /**
     * Get the value of netProfit
     */ 
     public function getNetProfit()
     {
         return $this->getGrossProfit() - 
             $this->getDiscountAmountBeforeTax() + 
             $this->getSurchargeAmountBeforeTax() - 
             $this->getPromoAmountBeforeTax() -
             $this->getFreeOfChargeAmountBeforeTax();
     }

    /**
     * Set the value of netProfit
     *
     * @return  self
     */ 
    public function setNetProfit($netProfit)
    {
        $this->netProfit = $netProfit;

        return $this;
    }
}
