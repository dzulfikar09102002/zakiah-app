<?php

namespace App\Helpers\Services\Product;

use App\Models\Product;
use App\Models\ProductImportService;
use App\Models\ProductStockMovement;
use Illuminate\Support\Facades\DB;

class ProductCogsCalculator
{
    protected ProductStockMovement $productStockMovement;

    public function __construct(ProductStockMovement $productStockMovement)
    {
        $this->productStockMovement = $productStockMovement;
    }

    public function calculate()
    {
        if ($this->productStockMovement->stock_out > $this->productStockMovement->stock_in) {
            return;
        }

        $cogs = ProductStockMovement::where('product_id', $this->productStockMovement->product_id)
            ->whereIn('resource_type', [ProductImportService::class, Product::class])
            ->whereRaw('stock_in > stock_out')
            ->select(DB::raw('SUM(buying_price * stock_in) / SUM(stock_in) AS cogs'))
            ->first();

        if (!$cogs || !$cogs['cogs']) {
            return;
        }

        Product::where('id', $this->productStockMovement->product_id)->update(['cost_of_goods_sold' => $cogs['cogs']]);
    }
}