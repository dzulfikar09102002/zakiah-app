<?php

use App\Helpers\UniqueCodeGenerator;
use App\Models\Location;
use App\Models\OrderType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductLocationStock;
use App\Models\ProductStockMovement;
use App\Models\ProductUnit;
use Illuminate\Support\Facades\Log;

function importLocation(string $locationName, $entityId): array
{
    $created = false;
    $search_name = UniqueCodeGenerator::generateSearchName($locationName);
    $data = Location::where('entity_id', $entityId)->where('search_name', $search_name)->first();
    if (!$data) {
        $created = true;
        $data = new Location();
        $data->entity_id = $entityId;
        $data->code = UniqueCodeGenerator::generateCode();
        $data->name = $locationName;
        $data->search_name = strtolower($data->name);
        $data->timezone = 'Asia/Jakarta';
        $data->save();
    };

    return [
        'data' => $data,
        'created' => $created,
    ];
}

function importProductUnit($name, $entityId): array
{
    $created = false;
    $search_name = UniqueCodeGenerator::generateSearchName($name);
    $productUnit = ProductUnit::where('entity_id', $entityId)->where('search_name', $search_name)->first();
    if (!$productUnit) {
        $created = true;
        $productUnit = new ProductUnit();
        $productUnit->entity_id = $entityId;
        $productUnit->name = $name;
        $productUnit->search_name = $search_name;
        $productUnit->save();
    };

    return [
        'data' => $productUnit,
        'created' => $created,
    ];
}

function importProductCategory($name, $entityId): array
{
    $created = false;
    $search_name = UniqueCodeGenerator::generateSearchName($name);
    $data = ProductCategory::where('entity_id', $entityId)->where('search_name', $search_name)->first();
    if (!$data) {
        $created = true;
        $data = new ProductCategory();
        $data->entity_id = $entityId;
        $data->name = $name;
        $data->search_name = $search_name;
        $data->save();
    };

    return [
        'data' => $data,
        'created' => $created,
    ];
}

function importProduct(array $row, Location $location, ProductUnit $productUnit, ProductCategory $productCategory, ?int $sellPrice, $entityId): array
{
    $created = false;
    $product = Product::where('entity_id', $entityId)->where('barcode', $row[2])->first();
    $cogs = ((int) preg_replace("/[^0-9]+/", "", $row[7]));
    if (!$product) {
        $created = true;
        $product = new Product();
        $product->entity_id = $entityId;
        $product->cost_of_goods_sold = $cogs;
    }

    $product->code = $row[1];
    $product->name = $row[3];
    $product->description = '';
    $product->sku = $row[2];
    $product->barcode = $row[2];
    $product->product_unit_id = $productUnit->id;
    $product->product_sell_unit_id = $productUnit->id;
    $product->product_category_id = $productCategory->id;
    $product->location_id = $location->id;
    $product->sell_price = $sellPrice ?? 0;
    $product->last_buying_price = $cogs;

    $product->save();

    return [
        'data' => $product,
        'created' => $created,
    ];
}

function importOrderType(string $name, $entityId): array
{
    $created = false;
    $search_name = UniqueCodeGenerator::generateSearchName($name);
    $data = OrderType::where('entity_id', $entityId)->where('search_name', $search_name)->first();
    if (!$data) {
        $created = true;
        $data = new OrderType();
        $data->entity_id = $entityId;
    };

    $data->name = $name;
    $data->fixed_fee = 0;
    $data->variable_fee = 0;
    $data->search_name = $search_name;
    $data->save();

    return [
        'data' => $data,
        'created' => $created,
    ];
}

function importProductSellPrice(Location $location, Product $product, ProductUnit $productUnit, OrderType $orderType, int $sellPrice)
{
    $productLocationStock = $product->productSellPrices()->firstOrNew([
        'location_id' => $location->id,
        'order_type_id' => $orderType->id,
        'product_unit_id' => $productUnit->id,
    ]);

    $productLocationStock->sell_price = $sellPrice ?? 0;
    $productLocationStock->save();
}

function importStockMovement(Location $location, Product $product, ProductUnit $productUnit, int $stock)
{
    $data = new ProductStockMovement();

    $data->product_id = $product->id;
    $data->location_id = $location->id;
    $data->product_unit_id = $productUnit->id;

    $data->original_product_unit_id = $productUnit->id;

    $data->resource_id = 0;
    $data->resource_type = 'ManualImport';

    $data->original_stock_out = 0;
    $data->original_stock_in = $stock ?? 0;
    $data->original_buying_price = $product->cost_of_goods_sold;
    $data->conversion_stock = 1; # should find conversion, not for now

    $data->stock_in = $data->original_stock_in * $data->conversion_stock;
    $data->stock_out = $data->original_stock_out * $data->conversion_stock;
    $data->buying_price = $data->original_buying_price * $data->conversion_stock;

    $data->save();
}

function importProductLocation(Location $location, Product $product)
{
    $productLocationStock = $product->productLocations()->firstOrNew([
        'location_id' => $location->id,
    ]);

    $productLocationStock->available_stock_pos = ProductLocationStock::where([
        'product_id' => $product->id,
        'location_id' => $location->id,
    ])->where('stock', '>', 0)->count('id') > 0;

    $productLocationStock->save();
}

$entityId = 1;

$csvFileName = "master_produk_mojosari_4.csv";
$csvFile = public_path($csvFileName);

$file_handle = fopen($csvFile, 'r');

$locations = [];
$row = 0;
Illuminate\Support\Facades\DB::beginTransaction();
try {
    while ($data = fgetcsv($file_handle, null, ";")) {
        $row = $row + 1;
        if ($row == 1) {
            $locationNames = array_slice($data, 12);
            foreach ($locationNames as $name)
            {
                array_push($locations, importLocation(str_replace('_', ' ', $name), $entityId)['data']);
            }

            continue;
        }
        Illuminate\Support\Facades\Log::info("row >> " . $row);

        $productUnitImport = importProductUnit($data[5], $entityId);
        $productCategoryImport = importProductCategory($data[10], $entityId);

        $sellPrice = preg_replace("/[^0-9]+/", "", $data[8]);
        $productImport = importProduct($data, $locations[0], $productUnitImport['data'], $productCategoryImport['data'], ((int) $sellPrice), $entityId);
        $orderTypeEcerImport = importOrderType('Ecer', $entityId);

        foreach ($locations as $location)
        {
            $product =  $productImport['data'];
            $productUnit = $productUnitImport['data'];
            $orderType = $orderTypeEcerImport['data'];

            importProductSellPrice($location, $product, $productUnit, $orderType, ((int) $sellPrice));
     
        }

        $location = $locations[0];
        $stock = preg_replace("/[^0-9]+/", "", $data[12] ?? '0');

        importStockMovement($location, $product, $productUnit, ((int) $stock));
        importProductLocation($location, $product);
    }

    Illuminate\Support\Facades\DB::commit();
} catch (Exception $e) {
    Log::error($e->getMessage());
    Illuminate\Support\Facades\DB::rollBack();
}

fclose($file_handle);
