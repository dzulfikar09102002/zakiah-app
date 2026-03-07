<?php

namespace App\Observers;

use App\Models\ProductLocation;

class ProductLocationObserver
{
    public function creating(ProductLocation $productLocation): void
    {
        //
        $productLocation->checksum = $this->generateChecking($productLocation);
    }
    
    public function updating(ProductLocation $productLocation): void
    {
        //
        $productLocation->checksum = $this->generateChecking($productLocation);
    }
    
    public function deleting(ProductLocation $productLocation): void
    {
        //
        $productLocation->checksum = $this->generateChecking($productLocation);
    }

    private function generateChecking(ProductLocation $productLocation) : string {
        return md5(
            $productLocation->product_id . '-' .
            $productLocation->location_id . '-' .
            $productLocation->deleted_at ?? 0
        );
    }
}
