<?php

namespace App\Helpers\Services\CustomerOrder;

use App\Helpers\Data\Customer\CustomerData;
use App\Helpers\Data\CustomerOrder\CustomerOrder as CustomerOrderData;
use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderDetail;
use App\Models\CustomerOrderPromo;

class CustomerOrderCreator
{
    private CustomerOrderData $customerOrderData;
    
    /**
     * Create a new class instance.
     */
    public function __construct(CustomerOrderData $customerOrderData)
    {
        //
        $this->customerOrderData = $customerOrderData;
    }

    public function create(): CustomerOrder
    {
        $customerOrder = CustomerOrder::firstOrNew([
            'code' => $this->customerOrderData->getCode(),
        ]);

        $customerOrder->entity_id = $this->customerOrderData->getEntity()->id;
        $customerOrder->location_id = $this->customerOrderData->getLocation()->id;
        $customerOrder->device_id = $this->customerOrderData->getDevice()->id;
        $customerOrder->checkpoint_device_id = $customerOrder->device_id;
        $customerOrder->order_type_id = $this->customerOrderData->getOrderType()->id;
        $customerOrder->customer_id = $this->createCustomer($this->customerOrderData->getCustomerData())?->id;

        $customerOrder->subtotal = $this->customerOrderData->getSubTotal();
        $customerOrder->tax_inclusive_amount = $this->customerOrderData->getTaxInclusiveAmount();
        $customerOrder->tax_exclusive_amount = $this->customerOrderData->getTaxExclusiveAmount();
        $customerOrder->service_charge = $this->customerOrderData->getServiceCharge();
        $customerOrder->promo_amount = $this->customerOrderData->getPromoAmount();
        $customerOrder->discount_amount = $this->customerOrderData->getDiscountAmount();
        $customerOrder->surcharge_amount = $this->customerOrderData->getSurchargeAmount();
        $customerOrder->free_of_charge_amount = $this->customerOrderData->getFreeOfChargeAmount();
        $customerOrder->rounding_amount = $this->customerOrderData->getRoundingAmount();
        $customerOrder->payment_platform_fee = $this->customerOrderData->getPaymentPlatformFee();
        $customerOrder->platform_fee = $this->customerOrderData->getPlatformFee();
        $customerOrder->delivery_fee = $this->customerOrderData->getDeliveryFee();
        $customerOrder->promo_delivery_fee = $this->customerOrderData->getPromoDeliveryFee();
        $customerOrder->total_amount = $this->customerOrderData->getTotalAmount();
        $customerOrder->notes = $this->customerOrderData->getNotes();
        $customerOrder->adjustment = $this->customerOrderData->getAdjustment()?->toArray();
        $customerOrder->product_ids = [];
        $customerOrder->product_category_ids = [];
        $customerOrder->modifier_ids = [];
        $customerOrder->modifier_option_ids = [];

        $customerOrder->save();

        $this->createLines($customerOrder);
        $this->createPromoOrder($customerOrder);

        return $customerOrder;
    }

    private function createLines(CustomerOrder $customerOrder)
    {
        //
        foreach ($this->customerOrderData->getCustomerOrderLines() as $line)
        {
            $customerOrderLine = CustomerOrderDetail::firstOrNew([
                'id' => $line->getId(),
            ]);

            if ($line->getDestroy() && $customerOrderLine->id != null) {
                $customerOrderLine->delete();
                continue;
            }

            $customerOrderLine->customer_order_id = $customerOrder->id;
            $customerOrderLine->brand_id = $line->getBrand()?->id;
            $customerOrderLine->customer_id = $customerOrder->customer_id;
            $customerOrderLine->employee_id = $line->getEmployee()->id;
            $customerOrderLine->order_type_id = $line->getOrderType()->id;
            $customerOrderLine->product_id = $line->getProduct()->id;
            $customerOrderLine->product_category_id = $line->getProductCategory()?->id;
            $customerOrderLine->product_unit_id = $line->getProductUnit()->id;
            $customerOrderLine->tax_id = $line->getTax()?->id;

            $customerOrderLine->product_name = $line->getProduct()->name;
            $customerOrderLine->product_category_name = $line->getProductCategory()?->name;
            $customerOrderLine->product_unit_name = $line->getProductUnit()->name;

            $customerOrderLine->sell_price = $line->getSellPrice();
            $customerOrderLine->custom_price = $line->getCustomPrice();
            $customerOrderLine->quantity = $line->getQuantity();

            $customerOrderLine->tax_rate = $line->getTax()?->rate;
            $customerOrderLine->tax_inclusive_amount = $line->getTaxInclusiveAmount();
            $customerOrderLine->tax_exclusive_amount = $line->getTaxExclusiveAmount();

            $customerOrderLine->service_charge = $line->getServiceCharge();
            $customerOrderLine->service_charge_rate = $line->getServiceChargeRate();
            $customerOrderLine->service_charge_include_tax = $line->getServiceChargeIncludeTax();

            $customerOrderLine->promo_amount = $line->getPromoAmount();
            $customerOrderLine->discount_amount = $line->getDiscountAmount();
            $customerOrderLine->surcharge_amount = $line->getSurchargeAmount();
            $customerOrderLine->free_of_charge_amount = $line->getFreeOfChargeAmount();

            $customerOrderLine->prorate_promo_amount = $line->getProratePromoAmount();
            $customerOrderLine->prorate_discount_amount = $line->getProrateDiscountAmount();
            $customerOrderLine->prorate_surcharge_amount = $line->getProrateSurchargeAmount();
            $customerOrderLine->prorate_free_of_charge_amount = $line->getProrateFreeOfChargeAmount();

            $customerOrderLine->total_line_amount = $line->getTotalLineAmount();
            $customerOrderLine->total_amount = $line->getTotalAmount();

            $customerOrderLine->adjustment = $line->getAdjustment()?->toArray();

            $customerOrderLine->promo_id = $line->getPromo()?->getPromoId();
            $customerOrderLine->promo_name = $line->getPromo()?->getPromoName();

            $customerOrderLine->notes = $line->getNotes();

            $customerOrderLine->loyalty_id = $line->getLoyaltyId();
            $customerOrderLine->loyalty_reward_product_id = $line->getLoyaltyRewardProductId();
            $customerOrderLine->loyalty_point = $line->getLoyaltyRewardProductPoint();

            $customerOrderLine->save();
        }
    }

    private function createCustomer(?CustomerData $data): ?Customer
    {
        return (new CustomerFinder($data, $this->customerOrderData->getSubTotal()))->create();
    }

    private function createPromoOrder(CustomerOrder $customerOrder)
    {
        foreach ($this->customerOrderData->getPromos() as $promo) {
            $customerOrderPromo = new CustomerOrderPromo();
            $customerOrderPromo->customer_order_id = $customerOrder->id;
            $customerOrderPromo->fill([
                'promo_id' => $promo->getPromoId(),
                'promo_name' => $promo->getPromoName(),
                'promo_reward_id' => $promo->getPromoRewadId(),
                'product_id' => $promo->getProductId(),
                'product_category_id' => $promo->getProductCategoryId(),
                'promo_reward_id' => $promo->getPromoRewadId(),
                'amount' => $promo->getAmount(),
                'applied_promo_amount' => $promo->getAppliedPromoAmount(),
                'promo_reward_template' => $promo->getPromoRewardTemplate(),
                'promo_reward_percentage' => $promo->getPromoRewardPercentage(),
                'promo_reward_amount' => $promo->getPromoRewardAmount(),
                'promo_reward_maximum_amount' => $promo->getPromoRewardMaximumAmount(),
            ]);

            $customerOrderPromo->save();
        }
    }
}
