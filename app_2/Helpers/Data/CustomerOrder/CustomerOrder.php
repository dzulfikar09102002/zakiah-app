<?php

namespace App\Helpers\Data\CustomerOrder;

use App\Helpers\Data\Customer\CustomerData;
use App\Helpers\Data\Promo\PromoData;
use App\Models\CustomerCategoryRule;
use App\Models\Device;
use App\Models\Entity;
use App\Models\Location;
use App\Models\OrderType;

class CustomerOrder
{
    private Entity $entity;
    private Location $location;
    private Device $device;
    private OrderType $orderType;
    private ?CustomerData $customerData;
    private ?CustomerCategoryRule $customerCategoryRule;
    /**
     * 
     * @var CustomerOrderLine[]
     * 
     */
    private array $customerOrderLines;
    /**
     * @var int[]
     */
    private array $promoIds;
    /**
     * @var PromoData[]
     */
    private array $promos;

    private ?Adjustment $adjustment;
    private int $subTotal, $taxInclusiveAmount, $taxExclusiveAmount, $serviceCharge, $totalItem;
    private int $promoAmount, $discountAmount, $surchargeAmount, $freeOfChargeAmount, $roundingAmount;
    private int $platformFee, $paymentPlatformFee, $deliveryFee, $promoDeliveryFee, $totalAmount, $serviceChargeRate;
    private bool $serviceChargeIncludeTax;
    private string $code;
    private ?string $notes;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
        $this->promos = array();
        $this->promoIds = array();
    }

    /**
     * Get the value of entity
     */ 
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Set the value of entity
     *
     * @return  self
     */ 
    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Get the value of location
     */ 
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set the value of location
     *
     * @return  self
     */ 
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get the value of device
     */ 
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * Set the value of device
     *
     * @return  self
     */ 
    public function setDevice($device)
    {
        $this->device = $device;

        return $this;
    }

    /**
     * Get the value of orderType
     */ 
    public function getOrderType()
    {
        return $this->orderType;
    }

    /**
     * Set the value of orderType
     *
     * @return  self
     */ 
    public function setOrderType($orderType)
    {
        $this->orderType = $orderType;

        return $this;
    }

    /**
     * @return CustomerOrderLine[]
     */ 
    public function getCustomerOrderLines(): array
    {
        return $this->customerOrderLines;
    }

    /**
     * 
     * @param CustomerOrderLine[] $customerOrderLines
     * @return  self
     */ 
    public function setCustomerOrderLines(array $customerOrderLines)
    {
        $this->customerOrderLines = $customerOrderLines;

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
     * Get the value of subTotal
     */ 
    public function getSubTotal()
    {
        return $this->subTotal;
    }

    /**
     * Set the value of subTotal
     *
     * @return  self
     */ 
    public function setSubTotal($subTotal)
    {
        $this->subTotal = $subTotal;

        return $this;
    }

    /**
     * Get the value of customerCategoryRule
     */ 
    public function getCustomerCategoryRule()
    {
        return $this->customerCategoryRule;
    }

    /**
     * Set the value of customerCategoryRule
     *
     * @return  self
     */ 
    public function setCustomerCategoryRule($customerCategoryRule)
    {
        $this->customerCategoryRule = $customerCategoryRule;

        return $this;
    }

    /**
     * Get the value of taxInclusiveAmount
     */ 
    public function getTaxInclusiveAmount()
    {
        return $this->taxInclusiveAmount;
    }

    /**
     * Set the value of taxInclusiveAmount
     *
     * @return  self
     */ 
    public function setTaxInclusiveAmount($taxInclusiveAmount)
    {
        $this->taxInclusiveAmount = $taxInclusiveAmount;

        return $this;
    }

    /**
     * Get the value of taxExclusiveAmount
     */ 
    public function getTaxExclusiveAmount()
    {
        return $this->taxExclusiveAmount;
    }

    /**
     * Set the value of taxExclusiveAmount
     *
     * @return  self
     */ 
    public function setTaxExclusiveAmount($taxExclusiveAmount)
    {
        $this->taxExclusiveAmount = $taxExclusiveAmount;

        return $this;
    }

    /**
     * Get the value of serviceCharge
     */ 
    public function getServiceCharge()
    {
        return $this->serviceCharge;
    }

    /**
     * Set the value of serviceCharge
     *
     * @return  self
     */ 
    public function setServiceCharge($serviceCharge)
    {
        $this->serviceCharge = $serviceCharge;

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
     * Get the value of platformFee
     */ 
    public function getPlatformFee()
    {
        return $this->platformFee;
    }

    /**
     * Set the value of platformFee
     *
     * @return  self
     */ 
    public function setPlatformFee($platformFee)
    {
        $this->platformFee = $platformFee;

        return $this;
    }

    /**
     * Get the value of deliveryFee
     */ 
    public function getDeliveryFee()
    {
        return $this->deliveryFee;
    }

    /**
     * Set the value of deliveryFee
     *
     * @return  self
     */ 
    public function setDeliveryFee($deliveryFee)
    {
        $this->deliveryFee = $deliveryFee;

        return $this;
    }

    /**
     * Get the value of promoDeliveryFee
     */ 
    public function getPromoDeliveryFee()
    {
        return $this->promoDeliveryFee;
    }

    /**
     * Set the value of promoDeliveryFee
     *
     * @return  self
     */ 
    public function setPromoDeliveryFee($promoDeliveryFee)
    {
        $this->promoDeliveryFee = $promoDeliveryFee;

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
        $this->totalAmount = $this->getSubTotal() - $this->getPromoAmount() - $this->getDiscountAmount() + $this->getSurchargeAmount() + $this->getPaymentPlatformFee();

        return $this;
    }

    /**
     * Get the value of serviceChargeRate
     */ 
    public function getServiceChargeRate()
    {
        return $this->serviceChargeRate;
    }

    /**
     * Set the value of serviceChargeRate
     *
     * @return  self
     */ 
    public function setServiceChargeRate($serviceChargeRate)
    {
        $this->serviceChargeRate = $serviceChargeRate;

        return $this;
    }

    /**
     * Get the value of serviceChargeIncludeTax
     */ 
    public function getServiceChargeIncludeTax()
    {
        return $this->serviceChargeIncludeTax;
    }

    /**
     * Set the value of serviceChargeIncludeTax
     *
     * @return  self
     */ 
    public function setServiceChargeIncludeTax($serviceChargeIncludeTax)
    {
        $this->serviceChargeIncludeTax = $serviceChargeIncludeTax;

        return $this;
    }

    private function getAdjustmentAmount(): int
    {
        $adjustmentAmount = $this->getAdjustment()?->getAmount() ?? 0;
        if ($adjustmentAmount < 0 && $this->getSubTotal() <= abs($adjustmentAmount))
        {
            $adjustmentAmount = $this->getSubTotal() * -1;
        }

        return $adjustmentAmount;
    }

    /**
     * Get the value of freeOfChargeAmount
     */ 
    public function getFreeOfChargeAmount()
    {
        return $this->freeOfChargeAmount;
    }

    /**
     * Set the value of freeOfChargeAmount
     *
     * @return  self
     */ 
    public function setFreeOfChargeAmount($freeOfChargeAmount)
    {
        $this->freeOfChargeAmount = $freeOfChargeAmount;

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
     * Get the value of promoIds
     */ 
    public function getPromoIds()
    {
        return $this->promoIds;
    }

    /**
     * Set the value of promoIds
     *
     * @param int[] $promoIds
     * @return  self
     */ 
    public function setPromoIds($promoIds)
    {
        $this->promoIds = $promoIds;

        return $this;
    }

    /**
     * Append the value of promoIds
     *
     * @param int $promoId
     * @return  self
     */ 
    public function appendPromoId($promoId)
    {
        array_push($this->promoIds, $promoId);

        return $this;
    }

    /**
     * Get the value of promos
     * 
     * @return PromoData[]
     * 
     */ 
    public function getPromos()
    {
        return $this->promos;
    }

    /**
     * Set the value of promos
     *
     * @param PromoData[] $promos
     * 
     * @return  self
     */ 
    public function setPromos(array $promos)
    {
        $this->promos = $promos;

        return $this;
    }

    public function appendPromo(PromoData $promo)
    {
        array_push($this->promos, $promo);

        return $this;
    }

    /**
     * Get the value of paymentPlatformFee
     */ 
    public function getPaymentPlatformFee()
    {
        return $this->paymentPlatformFee;
    }

    /**
     * Set the value of paymentPlatformFee
     *
     * @return  self
     */ 
    public function setPaymentPlatformFee($paymentPlatformFee)
    {
        $this->paymentPlatformFee = $paymentPlatformFee;

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

    public function toArray(): array
    {
        return [
            'subTotal' => $this->getSubTotal(),
            'totalItem' => $this->getTotalItem(),
            'taxInclusiveAmount' => $this->getTaxInclusiveAmount(),
            'taxExclusiveAmount' => $this->getTaxExclusiveAmount(),
            'serviceCharge' => $this->getServiceCharge(),
            'paymentPlatformFee' => $this->getPaymentPlatformFee(),
            'totalAmount' => $this->getTotalAmount(),
            'adjustment' => $this->getAdjustment()?->toArray(),
            'customer' => $this->getCustomerData()?->toArray(),
            'promos' => array_map(function(PromoData $line) { return $line->toArray(); }, $this->promos),
            'products' => array_map(function(CustomerOrderLine $line) { return $line->toArray(); }, $this->customerOrderLines)
        ];
    }

    /**
     * Get the value of totalItem
     */ 
    public function getTotalItem()
    {
        return $this->totalItem;
    }

    /**
     * Set the value of totalItem
     *
     * @return  self
     */ 
    public function setTotalItem($totalItem)
    {
        $this->totalItem = $totalItem;

        return $this;
    }
}
