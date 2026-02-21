<?php

namespace App\Helpers\Services\ProductOpname;

use App\Models\ProductLocationStock;
use App\Models\ProductOpnameService;
use App\Models\ProductOpnameServiceDetail;
use App\Models\ProductStockMovement;
use Exception;
use Illuminate\Support\Facades\DB;

class ProductOpnameApprovalService
{
    private ProductOpnameService $productOpnameService;

    /**
     * Create a new class instance.
     */
    public function __construct(ProductOpnameService $productOpnameService)
    {
        //
        $this->productOpnameService = $productOpnameService;
    }

    public function moveStock()
    {
        if ($this->productOpnameService->status != 'approved')
        {
            return;
        }

        # start transcation
        DB::beginTransaction();
        try {
            foreach($this->productOpnameService->productOpnameServiceDetails()->get() as $detail)
            {
                $this->createStockMovement($detail);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            throw $e;
        }
    }

    private function createStockMovement(ProductOpnameServiceDetail $detail)
    {
        if ($detail->difference_stock == 0) {
            return;
        }

        $productLocationStock = ProductLocationStock::where([
            'location_id' => $detail->location_id,
            'product_id' => $detail->product_id,
            'product_unit_id' => $detail->product_unit_id,
        ])->first();

        $data = new ProductStockMovement();

        $data->product_id = $detail->product_id;
        $data->location_id = $detail->location_id;
        $data->product_unit_id = $detail->product_unit_id;

        $data->original_product_unit_id = $detail->product_unit_id;

        $data->resource_id = $detail->id;
        $data->resource_type = $detail::class;

        if ($detail->difference_stock < 0) {
            $data->original_stock_out = abs($detail->difference_stock);
        }

        if ($detail->difference_stock > 0) {
            $data->original_stock_in = $detail->difference_stock;
        }
        $data->original_buying_price = $productLocationStock?->last_buy_price ?? 0;
        $data->conversion_stock = 1; # should find conversion, not for now

        $data->stock_in = $data->original_stock_in * $data->conversion_stock;
        $data->stock_out = $data->original_stock_out * $data->conversion_stock;
        $data->buying_price = $data->original_buying_price * $data->conversion_stock;

        $data->save();
    }
}
