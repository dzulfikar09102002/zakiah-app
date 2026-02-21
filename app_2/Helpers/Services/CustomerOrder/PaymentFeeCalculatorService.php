<?php

namespace App\Helpers\Services\CustomerOrder;

class PaymentFeeCalculatorService
{
    private array $paymentMethodIds;
    private int $subTotal;

    /**
     * Create a new class instance.
     * 
     * @param int[] $paymentMethodIds
     * 
     */
    public function __construct(array $paymentMethodIds, int $subTotal)
    {
        //
        $this->paymentMethodIds = $paymentMethodIds;
        $this->subTotal = $subTotal;
    }

    public function calculate(): int
    {
        

        return 0;
    }
}
