<?php

namespace App\Observers;

use App\Models\ProductSellPrice;

class ProductSellPriceObserver
{
    public function creating(ProductSellPrice $productSellPrice): void
    {
        //
        $productSellPrice->checksum = $this->generateChecking($productSellPrice);
    }
    
    public function updating(ProductSellPrice $productSellPrice): void
    {
        //
        $productSellPrice->checksum = $this->generateChecking($productSellPrice);
    }
    
    public function deleting(ProductSellPrice $productSellPrice): void
    {
        //
        $productSellPrice->checksum = $this->generateChecking($productSellPrice);
    }

    private function generateChecking(ProductSellPrice $productSellPrice) : string {
        return md5(
            $productSellPrice->product_id . '-' . 
            $productSellPrice->location_id . '-' . 
            $productSellPrice->product_unit_id . '-' . 
            $productSellPrice->order_type_id ?? 0 . '-' . 
            $productSellPrice->deleted_at ?? 0
        );
    }
}
