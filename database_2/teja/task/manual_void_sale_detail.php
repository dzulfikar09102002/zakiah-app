Illuminate\Support\Facades\DB::beginTransaction();
try {
    foreach (SaleTransactionDetail::whereIn('sale_transaction_id', [2943, 2941, 2936])->get() as $saleDetailTransaction)
    {
        # create movement
        $data = new ProductStockMovement();

        $data->product_id = $saleDetailTransaction->product_id;
        $data->location_id = 6;
        $data->product_unit_id = $saleDetailTransaction->product_unit_id;

        $data->original_product_unit_id = $saleDetailTransaction->product_unit_id;

        $data->resource_id = $saleDetailTransaction->id;
        $data->resource_type = $saleDetailTransaction::class;

        $data->original_stock_out = 0;
        $data->original_stock_in = $saleDetailTransaction->quantity;
        $data->original_buying_price = 0;
        $data->conversion_stock = 1; # should find conversion, not for now

        $data->stock_in = $data->original_stock_in * $data->conversion_stock;
        $data->stock_out = $data->original_stock_out * $data->conversion_stock;
        $data->buying_price = $data->original_buying_price * $data->conversion_stock;

        $data->save();
    }

    Illuminate\Support\Facades\DB::commit();
} catch (Exception $e) {
    Illuminate\Support\Log::error($e->getMessage());
    Illuminate\Support\Facades\DB::rollBack();
}
