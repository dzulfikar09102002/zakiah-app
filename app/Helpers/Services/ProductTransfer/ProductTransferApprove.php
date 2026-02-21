<?php

namespace App\Helpers\Services\ProductTransfer;

use App\Enums\StatusEnum;
use App\Models\Employee;
use App\Models\Location;
use App\Models\ProductStockMovement;
use App\Models\ProductTransferService;
use App\Models\ProductTransferServiceDetail;
use DateTime;
use DateTimeZone;

class ProductTransferApprove
{
    private ProductTransferService $productTransferService;
    private Employee $employee;

    /**
     * Create a new class instance.
     */
    public function __construct(int $productTransferId, int $empoyeeId)
    {
        //
        $this->productTransferService = ProductTransferService::find($productTransferId);
        $this->employee = Employee::find($empoyeeId);
    }

    public function approve($note = null)
    {
        $timeNow = new DateTime();
        $timezone = new DateTimeZone(Location::find($this->productTransferService->to_location_id)->timezone ?? 'UTC');
        $localTimeNow = (new DateTime())->setTimezone($timezone);
        
        $this->productTransferService->employee_approved_by = $this->employee->id;
        $this->productTransferService->approved_at = $timeNow;
        $this->productTransferService->local_approved_at = $localTimeNow;
        $this->productTransferService->approval_note = $note ?? 'Auto Approve by system';
        $this->productTransferService->status = StatusEnum::Approved;
        $this->productTransferService->save();

        foreach ($this->productTransferService->productTransferServiceDetails()->get() as $productTransferServiceDetail)
        {
            $this->createInStock($this->productTransferService->to_location_id, $productTransferServiceDetail);
        }
    }

    private function createInStock(int $toLocationId, ProductTransferServiceDetail $productTransferServiceDetail)
    {
        $data = new ProductStockMovement();
        $data = $data->fill([
            'product_id' => $productTransferServiceDetail->product_id,
            'location_id' => $toLocationId,
            'product_unit_id' => $productTransferServiceDetail->product_unit_id,
        ]);

        $data->original_product_unit_id = $productTransferServiceDetail->original_product_unit_id;

        $data->resource_id = $productTransferServiceDetail->id;
        $data->resource_type = $productTransferServiceDetail::class;

        $data->original_stock_out = 0;
        $data->original_stock_in = $productTransferServiceDetail->quantity;
        $data->original_buying_price = $productTransferServiceDetail->buying_price;
        $data->conversion_stock = 1; # should find conversion, not for now

        $data->stock_in = $data->original_stock_in * $data->conversion_stock;
        $data->stock_out = $data->original_stock_out * $data->conversion_stock;
        $data->buying_price = $data->original_buying_price * $data->conversion_stock;

        $data->save();
    }
}
