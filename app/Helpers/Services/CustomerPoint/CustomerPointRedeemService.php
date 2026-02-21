<?php

namespace App\Helpers\Services\CustomerPoint;

use App\Enums\CustomerPointTypeEnum;
use App\Helpers\Services\CustomerPoint\Helpers\CustomerPointValidateHelper;
use App\Helpers\UniqueCodeGenerator;
use App\Models\Customer;
use App\Models\CustomerPoint;
use App\Models\Loyalty;
use App\Models\SaleTransaction;

class CustomerPointRedeemService
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

    public function redeem(): int
    {
        if (!CustomerPointValidateHelper::validate($this->loyalty, $this->customer, $this->customerPoint)) {
            return 0;
        }

        if (!$this->validate()) {
            return 0;
        }

        $redeemPoint = 0;
        foreach($this->saleTransaction->saleTransactionDetailLoyalties()->get() as $saleTransactionDetail)
        {
            $loyaltyPoint = $saleTransactionDetail->loyalty_point * $saleTransactionDetail->quantity;
            if ($loyaltyPoint == 0) {
                continue;
            }

            # create customer point movement
            $this->customerPoint->customerPointMovements()->create([
                'sale_transaction_id' => $this->saleTransaction->id,
                'sale_transaction_detail_id' => $saleTransactionDetail->id,
                'location_id' => $this->saleTransaction->location_id,
                'customer_id' => $this->customer->id,
                'loyalty_id' => $saleTransactionDetail->loyalty_id,
                'loyalty_reward_product_id' => $saleTransactionDetail->loyalty_reward_product_id,
                'code' => UniqueCodeGenerator::generateCode(),
                'point' => $loyaltyPoint * -1,
                'type' => CustomerPointTypeEnum::Redeem,
            ]);

            $redeemPoint += $loyaltyPoint;
        }

        return $redeemPoint;
    }

    private function validate() : bool {
        return true;
    }
}
