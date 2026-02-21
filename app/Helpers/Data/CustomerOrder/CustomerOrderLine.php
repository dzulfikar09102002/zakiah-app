<?php

namespace App\Helpers\Data\CustomerOrder;

use App\Enums\TaxSettingEnum;
use App\Helpers\Data\Customer\CustomerData;
use App\Helpers\Data\Promo\PromoData;
use App\Models\Brand;
use App\Models\Employee;
use App\Models\OrderType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\Tax;

class CustomerOrderLine
{
    private OrderType $orderType;
    private ProductUnit $productUnit;
    private Product $product;
    private Employee $employee;
    private ?ProductCategory $productCategory;
    private ?Brand $brand;
    private ?Tax $tax;
    private ?TaxSettingEnum $taxSetting;
    private ?Adjustment $adjustment;
    private ?PromoData $promo;
    private ?CustomerData $customerData;
    // private ?Promo $promo;
    private int $quantity, $sellPrice, $totalLineAmount, $totalAmount, $serviceChargeRate, $modifierTotalAmount;
    private int $subTotalOrder, $promoAmountOrder, $discountAmountOrder, $surchargeAmountOrder;
    private int $proratePromoAmount, $prorateDiscountAmount, $prorateSurchargeAmount;
    private int $id, $customerOrderDetailId;
    private bool $serviceChargeIncludeTax, $customPrice, $destroy;
    private ?string $notes;
    private string $code;
    private ?int $loyaltyId, $loyaltyRewardProductId, $loyaltyRewardProductPoint;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the value of brand
     */ 
    public function getBrand(): ?Brand
    {
        return $this->brand;
    }

    /**
     * Set the value of brand
     *
     * @return  self
     */ 
    public function setBrand(?Brand $brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Get the value of orderType
     */ 
    public function getOrderType(): OrderType
    {
        return $this->orderType;
    }

    /**
     * Set the value of orderType
     *
     * @return  self
     */ 
    public function setOrderType(OrderType $orderType)
    {
        $this->orderType = $orderType;

        return $this;
    }

    /**
     * Get the value of productUnit
     */ 
    public function getProductUnit(): ProductUnit
    {
        return $this->productUnit;
    }

    /**
     * Set the value of productUnit
     *
     * @return  self
     */ 
    public function setProductUnit(ProductUnit $productUnit)
    {
        $this->productUnit = $productUnit;

        return $this;
    }

    /**
     * Get the value of product
     */ 
    public function getProduct(): Product
    {
        return $this->product;
    }

    /**
     * Set the value of product
     *
     * @return  self
     */ 
    public function setProduct(Product $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get the value of quantity
     */ 
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * Set the value of quantity
     *
     * @return  self
     */ 
    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get the value of adjustment
     */ 
    public function getAdjustment(): ?Adjustment
    {
        return $this->adjustment;
    }

    /**
     * Set the value of adjustment
     *
     * @return  self
     */ 
    public function setAdjustment(?Adjustment $adjustment)
    {
        $this->adjustment = $adjustment;

        return $this;
    }

    /**
     * Get the value of productCategory
     */ 
    public function getProductCategory(): ?ProductCategory
    {
        return $this->productCategory;
    }

    /**
     * Set the value of productCategory
     *
     * @return  self
     */ 
    public function setProductCategory(?ProductCategory $productCategory)
    {
        $this->productCategory = $productCategory;

        return $this;
    }

    /**
     * Get the value of tax
     */ 
    public function getTax(): ?Tax
    {
        return $this->tax;
    }

    /**
     * Set the value of tax
     *
     * @return  self
     */ 
    public function setTax(?Tax $tax)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * Get the value of sellPrice
     */ 
    public function getSellPrice(): int
    {
        return $this->sellPrice;
    }

    /**
     * Set the value of sellPrice
     *
     * @return  self
     */ 
    public function setSellPrice(int $sellPrice)
    {
        $this->sellPrice = $sellPrice;

        return $this;
    }

    /**
     * Get the value of subTotalOrder
     */ 
    public function getSubTotalOrder(): int
    {
        return $this->subTotalOrder;
    }

    /**
     * Set the value of subTotalOrder
     *
     * @return  self
     */ 
    public function setSubTotalOrder(int $subTotalOrder)
    {
        $this->subTotalOrder = $subTotalOrder;

        return $this;
    }

    /**
     * Get the value of totalLineAmount
     */ 
    public function getTotalLineAmount(): int
    {
        return $this->totalLineAmount;
    }

    /**
     * Set the value of totalLineAmount
     *
     * @return  self
     */ 
    public function setTotalLineAmount()
    {
        $this->totalLineAmount = ($this->sellPrice - $this->getPromoAmount() - $this->getDiscountAmount() + $this->getSurchargeAmount()) * $this->getQuantity();

        return $this;
    }

    /**
     * Get the value of taxSetting
     */ 
    public function getTaxSetting(): ?TaxSettingEnum
    {
        return $this->taxSetting;
    }

    /**
     * Set the value of taxSetting
     *
     * @return  self
     */ 
    public function setTaxSetting(?TaxSettingEnum $taxSetting)
    {
        $this->taxSetting = $taxSetting;

        return $this;
    }

    public function getDiscountAmount(): int
    {
        return $this->adjustment?->getDiscountAmount() ?? 0;
    }

    public function getSurchargeAmount(): int
    {
        return $this->adjustment?->getSurchargeAmount() ?? 0;
    }

    /**
     * Get the value of serviceChargeRate
     */ 
    public function getServiceChargeRate(): int
    {
        return $this->serviceChargeRate;
    }

    /**
     * Set the value of serviceChargeRate
     *
     * @return  self
     */ 
    public function setServiceChargeRate(int $serviceChargeRate)
    {
        $this->serviceChargeRate = $serviceChargeRate;

        return $this;
    }

    /**
     * Get the value of serviceChargeIncludeTax
     */ 
    public function getServiceChargeIncludeTax(): bool
    {
        return $this->serviceChargeIncludeTax;
    }

    /**
     * Set the value of serviceChargeIncludeTax
     *
     * @return  self
     */ 
    public function setServiceChargeIncludeTax(bool $serviceChargeIncludeTax)
    {
        $this->serviceChargeIncludeTax = $serviceChargeIncludeTax;

        return $this;
    }

    /**
     * Get the value of id
     */ 
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId(int $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of employee
     */ 
    public function getEmployee(): Employee
    {
        return $this->employee;
    }

    /**
     * Set the value of employee
     *
     * @return  self
     */ 
    public function setEmployee(Employee $employee)
    {
        $this->employee = $employee;

        return $this;
    }

    /**
     * Get the value of customPrice
     */ 
    public function getCustomPrice(): bool
    {
        return $this->customPrice;
    }

    /**
     * Set the value of customPrice
     *
     * @return  self
     */ 
    public function setCustomPrice(bool $customPrice)
    {
        $this->customPrice = $customPrice;

        return $this;
    }

    public function getTaxInclusiveAmount(): int
    {
        return 0;
    }

    public function getTaxExclusiveAmount(): int
    {
        return 0;
    }

    public function getServiceCharge(): int
    {
        return 0;
    }

    public function getPromoAmount(): int
    {
        return $this->promo?->getAppliedPromoAmount() ?? 0;
    }

    public function getFreeOfChargeAmount(): int
    {
        return 0;
    }

    public function getProrateFreeOfChargeAmount(): int
    {
        return 0;
    }

    /**
     * Get the value of notes
     */ 
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * Set the value of notes
     *
     * @return  self
     */ 
    public function setNotes(?string $notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get the value of promoAmountOrder
     */ 
    public function getPromoAmountOrder()
    {
        return $this->promoAmountOrder;
    }

    /**
     * Set the value of promoAmountOrder
     *
     * @return  self
     */ 
    public function setPromoAmountOrder($promoAmountOrder)
    {
        $this->promoAmountOrder = $promoAmountOrder;

        return $this->setProratePromoAmount(0);
    }

    /**
     * Get the value of discountAmountOrder
     */ 
    public function getDiscountAmountOrder()
    {
        return $this->discountAmountOrder;
    }

    /**
     * Set the value of discountAmountOrder
     *
     * @return  self
     */ 
    public function setDiscountAmountOrder($discountAmountOrder)
    {
        $this->discountAmountOrder = $discountAmountOrder;

        return $this->setProrateDiscountAmount(0);
    }

    /**
     * Get the value of surchargeAmountOrder
     */ 
    public function getSurchargeAmountOrder()
    {
        return $this->surchargeAmountOrder;
    }

    /**
     * Set the value of surchargeAmountOrder
     *
     * @return  self
     */ 
    public function setSurchargeAmountOrder($surchargeAmountOrder)
    {
        $this->surchargeAmountOrder = $surchargeAmountOrder;

        return $this->setProrateSurchargeAmount(0);
    }

    /**
     * Get the value of proratePromoAmount
     */ 
    public function getProratePromoAmount()
    {
        return $this->proratePromoAmount;
    }

    /**
     * Set the value of proratePromoAmount
     *
     * @return  self
     */ 
    public function setProratePromoAmount($proratePromoAmount)
    {
        $this->proratePromoAmount = $proratePromoAmount;

        return $this;
    }

    /**
     * Get the value of prorateDiscountAmount
     */ 
    public function getProrateDiscountAmount()
    {
        return $this->prorateDiscountAmount;
    }

    /**
     * Set the value of prorateDiscountAmount
     *
     * @return  self
     */ 
    public function setProrateDiscountAmount($prorateDiscountAmount)
    {
        $this->prorateDiscountAmount = $prorateDiscountAmount;

        return $this;
    }

    /**
     * Get the value of prorateSurchargeAmount
     */ 
    public function getProrateSurchargeAmount()
    {
        return $this->prorateSurchargeAmount;
    }

    /**
     * Set the value of prorateSurchargeAmount
     *
     * @return  self
     */ 
    public function setProrateSurchargeAmount($prorateSurchargeAmount)
    {
        $this->prorateSurchargeAmount = $prorateSurchargeAmount;

        return $this;
    }

    /**
     * Get the value of totalAmount
     */ 
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * Set the value of totalAmount
     *
     * @return  self
     */ 
    public function setTotalAmount()
    {
        $this->totalAmount = $this->getTotalLineAmount();
        $this->totalAmount += $this->getModifierTotalAmount();

        return $this;
    }

    /**
     * Get the value of modifierTotalAmount
     */ 
    public function getModifierTotalAmount()
    {
        return $this->modifierTotalAmount;
    }

    /**
     * Set the value of modifierTotalAmount
     *
     * @return  self
     */ 
    public function setModifierTotalAmount($modifierTotalAmount)
    {
        $this->modifierTotalAmount = $modifierTotalAmount;

        return $this;
    }

    /**
     * Get the value of promo
     */ 
    public function getPromo(): ?PromoData
    {
        return $this->promo;
    }

    /**
     * Set the value of promo
     *
     * @return  self
     */ 
    public function setPromo(?PromoData $promo)
    {
        $this->promo = $promo;

        return $this;
    }

    /**
     * Get the value of code
     */ 
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set the value of code
     *
     * @return  self
     */ 
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get the value of loyaltyId
     */ 
    public function getLoyaltyId(): ?int
    {
        return $this->loyaltyId;
    }

    /**
     * Set the value of loyaltyId
     *
     * @return  self
     */ 
    public function setLoyaltyId(?int $loyaltyId)
    {
        $this->loyaltyId = $loyaltyId;

        return $this;
    }

    /**
     * Get the value of loyaltyRewardProductId
     */ 
    public function getLoyaltyRewardProductId(): ?int
    {
        return $this->loyaltyRewardProductId;
    }

    /**
     * Set the value of loyaltyRewardProductId
     *
     * @return  self
     */ 
    public function setLoyaltyRewardProductId(?int $loyaltyRewardProductId)
    {
        $this->loyaltyRewardProductId = $loyaltyRewardProductId;

        return $this;
    }

    /**
     * Get the value of loyaltyRewardProductPoint
     */ 
    public function getLoyaltyRewardProductPoint()
    {
        return $this->loyaltyRewardProductPoint;
    }

    /**
     * Set the value of loyaltyRewardProductPoint
     *
     * @return  self
     */ 
    public function setLoyaltyRewardProductPoint($loyaltyRewardProductPoint)
    {
        $this->loyaltyRewardProductPoint = $loyaltyRewardProductPoint;

        return $this;
    }

    /**
     * Get the value of destroy
     */ 
    public function getDestroy()
    {
        return $this->destroy;
    }

    /**
     * Set the value of destroy
     *
     * @return  self
     */ 
    public function setDestroy($destroy)
    {
        $this->destroy = $destroy;

        return $this;
    }

    /**
     * Get the value of customerData
     */ 
    public function getCustomerData(): ?CustomerData
    {
        return $this->customerData;
    }

    /**
     * Set the value of customerData
     *
     * @return  self
     */ 
    public function setCustomerData(?CustomerData $customerData)
    {
        $this->customerData = $customerData;

        return $this;
    }

    public function toArray()
    {
        return [
            "customer_order_detail_id" => $this->getCustomerOrderDetailId(),
            "product_id" => $this->product->id,
            "brand_id" => $this->brand->id,
            "order_type_id" => $this->orderType->id,
            "product_unit_id" => $this->productUnit->id,
            "product_category_id" => $this->productCategory?->id,
            "catalogue_detail_id" => null,
            "quantity" => $this->quantity,
            "sell_price" => $this->sellPrice,
            "custom_price" => $this->customPrice,
            "adjustment" => $this->adjustment?->toArray(),
            "promo" => $this->promo?->toArray(),
            "product" => [
                "id" => $this->product->id,
                "name" => $this->product->name,
                "sku" => $this->product->sku,
                "code" => $this->product->code,
            ],
            "brand" => [
                "id" => $this->brand->id,
                "name" => $this->brand->name,
            ],
            "order_type" => [
                "id" => $this->orderType->id,
                "name" => $this->orderType->name,
            ],
            "product_unit" => [
                "id" => $this->productUnit->id,
                "name" => $this->productUnit->name,
            ],
            "product_category" => [
                "id" => $this->productCategory?->id,
                "name" => $this->productCategory?->name,
            ],
        ];
    }

    /**
     * Get the value of customerOrderDetailId
     */ 
    public function getCustomerOrderDetailId()
    {
        return $this->customerOrderDetailId;
    }

    /**
     * Set the value of customerOrderDetailId
     *
     * @return  self
     */ 
    public function setCustomerOrderDetailId($customerOrderDetailId)
    {
        $this->customerOrderDetailId = $customerOrderDetailId;

        return $this;
    }
}
