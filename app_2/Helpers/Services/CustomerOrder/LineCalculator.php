<?php

namespace App\Helpers\Services\CustomerOrder;

use App\Helpers\Data\CustomerOrder\Adjustment;
use App\Helpers\Data\CustomerOrder\CustomerOrderLine;
use App\Helpers\Data\CustomerOrder\ProductMap;
use App\Helpers\ProductSellPriceFinder;

class LineCalculator
{
    private ProductMap $products;
    private array $productPrices;
    private array $lines;
    private RelationBuilder $relationBuilder;

    private int $subtotal, $taxExclusiveAmount, $taxInclusiveAmount, $serviceCharge, $totalItem;

    /**
     * @var int[]
     */
    private array $productIds, $productCategoryIds;
    private array $productIdsWithQuantity, $productCategoryIdsWithQuantity;

    /**
     * Create a new class instance.
     */
    public function __construct(RelationBuilder $relationBuilder, array $lines)
    {
        //
        $this->relationBuilder = $relationBuilder;
        $this->lines = $lines;

        $this->products = $this->relationBuilder->getProducts();
        $this->productPrices = $this->relationBuilder->getProductPrices();

        $this->subtotal = 0;
        $this->totalItem = 0;
        $this->taxExclusiveAmount = 0;
        $this->taxInclusiveAmount = 0;
        $this->serviceCharge = 0;

        $this->productIds = [];
        $this->productCategoryIds = [];

        $this->productIdsWithQuantity = [];
        $this->productCategoryIdsWithQuantity = [];

        $this->calculate();
    }

    public function recalculate(array $lines)
    {
        $this->lines = $lines;
        $this->subtotal = 0;
        $this->totalItem = 0;
        $this->taxExclusiveAmount = 0;
        $this->taxInclusiveAmount = 0;
        $this->serviceCharge = 0;

        $this->productIds = [];
        $this->productCategoryIds = [];

        $this->productIdsWithQuantity = [];
        $this->productCategoryIdsWithQuantity = [];

        $this->calculate();
    }

    private function calculate()
    {
        foreach ($this->lines as $line)
        {
            if ($line['_destroy'] ?? false == true) {
                continue;
            }

            $foundProduct = $this->products->get($line['product_id']);
            $sellPrices = ProductSellPriceFinder::findProductSellPriceFromParam($foundProduct, $this->productPrices, $line);

            $customerOrderLine = new CustomerOrderLine();
            $customerOrderLine->setProduct($foundProduct);
            $customerOrderLine->setSellPrice($sellPrices);
            $customerOrderLine->setAdjustment($this->getAdjustment($line, $sellPrices));
            if (array_key_exists('promo', $line)) {
                $customerOrderLine->setPromo($line['promo']);
            } else {
                $customerOrderLine->setPromo(null);
            }
            $customerOrderLine->setQuantity($line['quantity']);
            $customerOrderLine->setModifierTotalAmount(0);
            $customerOrderLine->setTotalLineAmount();
            $customerOrderLine->setTotalAmount();

            if (!array_key_exists($foundProduct->id, $this->productIdsWithQuantity)) {
                $this->productIdsWithQuantity[$foundProduct->id] = 0;
            }
            $this->productIdsWithQuantity[$foundProduct->id] += $line['quantity'];

            if (!array_key_exists($foundProduct->product_category_id, $this->productCategoryIdsWithQuantity)) {
                $this->productCategoryIdsWithQuantity[$foundProduct->product_category_id] = 0;
            }
            $this->productCategoryIdsWithQuantity[$foundProduct->product_category_id] += $line['quantity'];

            array_push($this->productIds, $foundProduct->id);
            array_push($this->productCategoryIds, $foundProduct->product_category_id);

            $this->subtotal += $customerOrderLine->getTotalLineAmount();
            $this->totalItem += $customerOrderLine->getQuantity();
        }
    }

    /**
    * Get the value of totalItem
    */ 
    public function getTotalItem(): int
    {
        return $this->totalItem;
    }

    /**
    * Get the value of subtotal
    */ 
    public function getSubtotal(): int
    {
        return $this->subtotal;
    }

    /**
     * Get the value of taxInclusiveAmount
     */ 
    public function getTaxExclusiveAmount(): int
    {
        return $this->taxExclusiveAmount;
    }

    /**
     * Get the value of taxInclusiveAmount
     */ 
    public function getTaxInclusiveAmount(): int
    {
        return $this->taxInclusiveAmount;
    }

    /**
     * Get the value of serviceCharge
     */ 
    public function getServiceCharge()
    {
        return $this->serviceCharge;
    }

    /**
     * Get the value of productIds
     *
     * @return  int[]
     */ 
    public function getProductIds()
    {
        return $this->productIds;
    }

    /**
     * Get the value of productCategoryIds
     *
     * @return  int[]
     */ 
    public function getProductCategoryIds()
    {
        return $this->productCategoryIds;
    }

    private function getAdjustment(array $line, int $price): ?Adjustment
    {
        if (!array_key_exists('adjustment', $line)) {
            return null;
        }

        $adjustmentParam = $line['adjustment'];
        if ($adjustmentParam == null) {
            return null;
        }

        $promoId = null;
        if (array_key_exists('promo_id', $adjustmentParam)) {
            $promoId = $adjustmentParam['promo_id'];
        }

        return (new Adjustment())
                ->setQuantity($adjustmentParam['quantity'])
                ->setAmount($adjustmentParam['amount'])
                ->setFreeOfCharge($adjustmentParam['free_of_charge'])
                ->setIsPercentage($adjustmentParam['is_percentage'])
                ->setPromoId($promoId)
                ->setPrice($price);
    }

    /**
     * Get the value of productIdsWithQuantity
     */ 
    public function getProductIdsWithQuantity()
    {
        return $this->productIdsWithQuantity;
    }

    /**
     * Get the value of productCategoryIdsWithQuantity
     */ 
    public function getProductCategoryIdsWithQuantity()
    {
        return $this->productCategoryIdsWithQuantity;
    }
}
