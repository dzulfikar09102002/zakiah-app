<?php

namespace App\Observers;

use App\Helpers\Services\Product\ProductCogsCalculator;
use App\Models\Product;
use App\Models\ProductLocationStock;
use App\Models\ProductStockMovement;

class ProductStockMovementObserver
{
    /**
     * Handle the ProductStockMovement "created" event.
     */
    public function created(ProductStockMovement $productStockMovement): void
    {
        //
        $productLocationStock = ProductLocationStock::firstOrNew([
            'location_id' => $productStockMovement->location_id,
            'product_id' => $productStockMovement->product_id,
            'product_unit_id' => $productStockMovement->product_unit_id,
        ]);

        $originalBuyingPrice = $productStockMovement->original_buying_price;

        # new
        if ($productLocationStock->stock == null) {
            $productLocationStock->stock = 0;
            $productLocationStock->lowest_buy_price = $originalBuyingPrice;
            $productLocationStock->highest_buy_price = $originalBuyingPrice;
            $productLocationStock->average_buy_price = $originalBuyingPrice;
            $productLocationStock->save();
        }

        $productLocationStock = ProductLocationStock::where('location_id', $productStockMovement->location_id)
            ->where('product_id', $productStockMovement->product_id)
            ->where('product_unit_id', $productStockMovement->product_unit_id)
            ->lockForUpdate()
            ->first();

        $productLocationStock->average_buy_price = ($productLocationStock->average_buy_price + $originalBuyingPrice) / 2;
        if ($productLocationStock->lowest_buy_price > $originalBuyingPrice) {
            $productLocationStock->lowest_buy_price = $originalBuyingPrice;
        }

        if ($productLocationStock->highest_buy_price < $originalBuyingPrice) {
            $productLocationStock->highest_buy_price = $originalBuyingPrice;
        }
        
        $productLocationStock->last_buy_price = $originalBuyingPrice;
        $productLocationStock->last_in_stock = $productStockMovement->stock_in;
        $productLocationStock->last_out_stock = $productStockMovement->stock_out;

        $productLocationStock->stock += $productStockMovement->stock_in;
        $productLocationStock->stock -= $productStockMovement->stock_out;
        $productLocationStock->save();

        (new ProductCogsCalculator($productStockMovement))->calculate();
    }

    /**
     * Handle the ProductStockMovement "updated" event.
     */
    public function updated(ProductStockMovement $productStockMovement): void
    {
        //
    }

    /**
     * Handle the ProductStockMovement "deleted" event.
     */
    public function deleted(ProductStockMovement $productStockMovement): void
    {
        //
    }

    /**
     * Handle the ProductStockMovement "restored" event.
     */
    public function restored(ProductStockMovement $productStockMovement): void
    {
        //
    }

    /**
     * Handle the ProductStockMovement "force deleted" event.
     */
    public function forceDeleted(ProductStockMovement $productStockMovement): void
    {
        //
    }
}
