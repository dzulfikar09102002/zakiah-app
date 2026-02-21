<?php

namespace App\Observers;

use App\Models\ProductLocationStock;

class ProductLocationStockObserver
{
    public function creating(ProductLocationStock $productLocationStock): void
    {
        //
        $productLocationStock->checksum = $this->generateChecking($productLocationStock);
    }
    
    public function updating(ProductLocationStock $productLocationStock): void
    {
        //
        $productLocationStock->checksum = $this->generateChecking($productLocationStock);
    }
    
    public function deleting(ProductLocationStock $productLocationStock): void
    {
        //
        $productLocationStock->checksum = $this->generateChecking($productLocationStock);
    }

    private function generateChecking(ProductLocationStock $productLocationStock) : string {
        return md5(
            $productLocationStock->product_id . '-' . 
            $productLocationStock->location_id . '-' . 
            $productLocationStock->product_unit_id . '-' . 
            $productLocationStock->deleted_at ?? 0
        );
    }
}
