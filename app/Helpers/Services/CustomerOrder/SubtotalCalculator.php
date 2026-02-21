<?php

namespace App\Helpers\Services\CustomerOrder;

use App\Helpers\Data\CustomerOrder\CustomerOrderLine;
use App\Models\Product;

class SubtotalCalculator
{
    /**
     * 
     *  @var Product[]
     * 
     */
    private array $products;
    private array $lines;

    /**
     * Create a new class instance.
     */
    public function __construct(array $products, array $lines)
    {
        //
        $this->products = $products;
        $this->lines = $lines;
    }

    public function calculate(): int
    {
        $subtotal = 0;
        foreach ($this->lines as $line)
        {
            $foundProduct = $this->products[$line['id']];
            $sellPrices = $line['customerPrice'] ? $line['sell_price'] : $foundProduct->sell_price;

            $customerOrderLine = new CustomerOrderLine();
            $customerOrderLine->setProduct($foundProduct);
            $customerOrderLine->setSellPrice($sellPrices);
            $customerOrderLine->setAdjustment($line['adjustment']);
            $customerOrderLine->setQuantity($line['quantity']);
            $customerOrderLine->setLineAmount();
            $customerOrderLine->setTotalLine();

            $subtotal += $customerOrderLine->getTotalLine();
        }

        return $subtotal;
    }
}
