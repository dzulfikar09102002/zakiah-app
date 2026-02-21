<?php

namespace App\Helpers\Services\CustomerPoint;

use App\Enums\CustomerPointTypeEnum;
use App\Helpers\Services\CustomerPoint\Helpers\CustomerPointValidateHelper;
use App\Helpers\UniqueCodeGenerator;
use App\Models\Customer;
use App\Models\CustomerPoint;
use App\Models\Loyalty;
use App\Models\SaleTransaction;

class CustomerPointEarnService
{
    private SaleTransaction $saleTransaction;
    private ?Customer $customer;
    private ?CustomerPoint $customerPoint;
    private ?Loyalty $loyalty;
    
    /**
     * Create a new class instance.
     */
    public function __construct(SaleTransaction $saleTransaction, ?Loyalty $loyalty)
    {
        //
        $this->saleTransaction = $saleTransaction;

        $this->customer = $this->saleTransaction->customer()->first();
        $this->customerPoint = CustomerPointValidateHelper::findOrCreateCustomerPoint($this->customer);
        
        $this->loyalty = $loyalty;
    }

    public function earn(): int
    {
        if (!CustomerPointValidateHelper::validate($this->loyalty, $this->customer, $this->customerPoint)) {
            return 0;
        }

        if (!$this->validateEarn()) {
            return 0;
        }

        $earnPoint = $this->calculatePoint();
        if ($earnPoint == 0) {
            return 0;
        }

        # create customer point movement
        $this->customerPoint->customerPointMovements()->create([
            'sale_transaction_id' => $this->saleTransaction->id,
            'location_id' => $this->saleTransaction->location_id,
            'customer_id' => $this->customer->id,
            'loyalty_id' => $this->loyalty->id,
            'transaction_value' => $this->calculateTransactionValue(),
            'miniminal_transaction_value' => $this->loyalty->miniminal_transaction_value,
            'code' => UniqueCodeGenerator::generateCode(),
            'point' => $earnPoint,
            'type' => CustomerPointTypeEnum::Earn,
        ]);

        return $earnPoint;
    }

    public function validateEarn(): bool
    {
        # if purchase required

        return true;
    }

    private function calculatePoint(): int
    {
        # make sure not divided by zero / negative
        if ($this->loyalty->miniminal_transaction_value <= 0) {
            return 0;
        }

        $transactioValue = $this->calculateTransactionValue();
        $multiplication = floor($transactioValue / $this->loyalty->miniminal_transaction_value);
        if (!$this->loyalty->allow_multiple) {
            $multiplication = min($multiplication, 1);
        }

        return $multiplication * $this->loyalty->reward_point;
    }

    private function calculateTransactionValue(): int
    {
        $transactioValue = $this->saleTransaction->gross_sales + $this->saleTransaction->rounding_amount;
        if ($this->loyalty->include_discount_and_promo) {
            $transactioValue -= $this->saleTransaction->discount_amount_before_tax;
            $transactioValue -= $this->saleTransaction->promo_amount_before_tax;
        }

        if ($this->loyalty->include_surcharge) {
            $transactioValue += $this->saleTransaction->surcharge_amount_before_tax;
        }

        if ($this->loyalty->include_free_of_charge) {
            $transactioValue -= $this->saleTransaction->free_of_charge_amount_before_tax;
        }

        if ($this->loyalty->include_service_charge) {
            $transactioValue += $this->saleTransaction->service_charge_before_tax;
        }

        if ($this->loyalty->include_tax) {
            $transactioValue += $this->saleTransaction->tax_amount;
        }

        return $transactioValue;
    }
}
