<?php

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductStockMovement;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\Log;

function importStockMovement(Location $location, Product $product, ProductUnit $productUnit, int $stock)
{
    $data = new ProductStockMovement();

    $data->product_id = $product->id;
    $data->location_id = $location->id;
    $data->product_unit_id = $productUnit->id;
    $data->original_product_unit_id = $productUnit->id;

    $data->resource_id = 0;
    $data->resource_type = 'ManualImport';

    $data->original_stock_out = $stock ?? 0;
    $data->original_stock_in = 0;
    $data->original_buying_price = $product->cost_of_goods_sold;
    $data->conversion_stock = 1; # should find conversion, not for now

    $data->stock_in = $data->original_stock_in * $data->conversion_stock;
    $data->stock_out = $data->original_stock_out * $data->conversion_stock;
    $data->buying_price = $data->original_buying_price * $data->conversion_stock;

    $data->save();
}

$entityId = 1;

$csvFileName = "master_produk_mojosari_2.csv";
$csvFile = public_path($csvFileName);

$file_handle = fopen($csvFile, 'r');

$row = 0;
Illuminate\Support\Facades\DB::beginTransaction();
try {
    while ($data = fgetcsv($file_handle, null, ";")) {
        $row = $row + 1;
        if ($row == 1) {
            continue;
        }
        Illuminate\Support\Facades\Log::info("row >> " . $row);

        $product = Product::where('entity_id', $entityId)->where('barcode', $data[2])->first();
        Illuminate\Support\Facades\Log::info($product->id . ' = '. $product->name);
        $productUnit = $product->productUnit()->first();
        $location = Location::where('entity_id', $entityId)->where('id', 3)->first();
        Illuminate\Support\Facades\Log::info($location->id . ' = '.  $location->name);

        $stock = preg_replace("/[^0-9]+/", "", $data[12] ?? '0');

        importStockMovement($location, $product, $productUnit, ((int) $stock));
    }

    Illuminate\Support\Facades\DB::commit();
} catch (Exception $e) {
    Log::error($e->getMessage());
    Illuminate\Support\Facades\DB::rollBack();
}

fclose($file_handle);
