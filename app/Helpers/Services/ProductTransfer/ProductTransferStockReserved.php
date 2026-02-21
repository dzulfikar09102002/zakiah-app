<?php

namespace App\Helpers\Services\ProductTransfer;

use App\Models\ProductStockMovement;
use App\Models\ProductTransferService;
use App\Models\ProductTransferServiceDetail;

class ProductTransferStockReserved
{
    private ProductTransferService $productTransferService;

    /**
     * Create a new class instance.
     */
    public function __construct(int $productTransferId)
    {
        //
        $this->productTransferService = ProductTransferService::find($productTransferId);
    }

    public function reserved($note = null)
    {
        foreach ($this->productTransferService->productTransferServiceDetails()->get() as $productTransferServiceDetail)
        {
            $this->createStockMovement($this->productTransferService->from_location_id, $productTransferServiceDetail);
        }
    }

    private function createStockMovement(int $fromLocationId, ProductTransferServiceDetail $productTransferServiceDetail)
    {
        # validate
        $this->validateOutStock($productTransferServiceDetail->product_id, $productTransferServiceDetail->product_unit_id, $fromLocationId, $productTransferServiceDetail->quantity);

        $data = new ProductStockMovement();
        $data = $data->fill([
            'product_id' => $productTransferServiceDetail->product_id,
            'location_id' => $fromLocationId,
            'product_unit_id' => $productTransferServiceDetail->product_unit_id,
        ]);

        $data->original_product_unit_id = $productTransferServiceDetail->original_product_unit_id;

        $data->resource_id = $productTransferServiceDetail->id;
        $data->resource_type = $productTransferServiceDetail::class;

        $data->original_stock_out = $productTransferServiceDetail->quantity;
        $data->original_stock_in = 0;
        $data->original_buying_price = $productTransferServiceDetail->buying_price;
        $data->conversion_stock = 1; # should find conversion, not for now

        $data->stock_in = $data->original_stock_in * $data->conversion_stock;
        $data->stock_out = $data->original_stock_out * $data->conversion_stock;
        $data->buying_price = $data->original_buying_price * $data->conversion_stock;

        $data->save();
    }

    private function validateOutStock(int $productId, int $productUnitId, int $locationId, int $quantity)
    {

    }
}
