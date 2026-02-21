<?php

namespace App\Helpers\Services\Promo;

use App\Helpers\Data\CustomerOrder\CustomerOrder as CustomerOrderData;
use App\Helpers\Services\CustomerOrder\LineCalculator;
use App\Models\Promo;

class PromoValidator
{
    private CustomerOrderData $customerOrderData;
    private LineCalculator $lineCalculator;
    private Promo $promo;

    /**
     * Create a new class instance.
     */
    public function __construct(Promo $promo, CustomerOrderData $customerOrderData, LineCalculator $lineCalculator)
    {
        //
        $this->customerOrderData = $customerOrderData;
        $this->lineCalculator = $lineCalculator;
        $this->promo = $promo;
    }

    public function validate() : bool {
        return $this->validateMinimumSalePurchase() && 
            $this->validateProducts() && 
            $this->validateProductCategories() && 
            $this->validateCustomerCategories() && 
            $this->validateOrderType();
    }

    private function validateMinimumSalePurchase(): bool
    {
        $minimumSalesPurchase = $this->promo['promoRule']->minimum_sales_purchase;
        if ($minimumSalesPurchase == null) {
            return true;
        }

        return $this->customerOrderData->getSubTotal() > $minimumSalesPurchase;
    }

    private function validateCustomerCategories(): bool
    {
        $forValidates = $this->promo['promoRule']['promoRuleCustomerCategories']->pluck('customer_category_id')->toArray();
        if (count($forValidates) == 0) {
            return true;
        }

        $customer = $this->customerOrderData->getCustomerData();
        if ($customer == null || $customer->getCustomerCategory() == null) {
            return count($forValidates) == 0;
        }
        
        return in_array($customer->getCustomerCategory()->id, $forValidates);
    }

    private function validateOrderType(): bool
    {
        $forValidates = $this->promo['promoRule']['promoRuleOrderTypes']->pluck('order_type_id')->toArray();
        if (count($forValidates) == 0) {
            return true;
        }

        return in_array($this->customerOrderData->getOrderType()->id, $forValidates);
    }

    private function validateProducts(): bool
    {
        return true;
    }

    private function validateProductCategories(): bool
    {
        return true;
    }
}
