<?php

namespace App\Imports;

use App\Helpers\UniqueCodeGenerator;
use App\Models\Entity;
use App\Models\Location;
use App\Models\OrderType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImportService;
use App\Models\ProductImportServiceDetail;
use App\Models\ProductLocationStock;
use App\Models\ProductStockMovement;
use App\Models\ProductUnit;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class ProductImportServiceBarangImport implements OnEachRow, WithHeadingRow, WithChunkReading
{
    private Entity $entity;
    private ProductImportService $importService;

    /**
     * TODO
     * make only insert line
     * insert detail product on different service
     */
    public function __construct(Entity $entity, ProductImportService $importService)
    {
        $this->entity = $entity;
        $this->importService = $importService;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if (!isset($row['barcode'])) {
            return null;
        }

        return new ProductImportServiceDetail($this->getParams($row));
    }

    public function onRow(Row $row)
    {
        $this->importDetail($row);
    }

    public function importDetail(Row $row)
    {
        if (!isset($row['barcode'])) {
            return null;
        }

        $rowIndex = $row->getIndex();
        $data = $row->toArray();
        $minColumns = 13;

        if (count($data) < $minColumns || !isset($data['barcode'])) {
            $error = ValidationException::withMessages([
                'Kolom' => ['Jumlah kolom kurang'],
             ]);

             throw $error;
        }

        $locations = [];
        $locationNames = array_slice($data, 12);
        foreach ($locationNames as $name => $stock)
        {
            if ($name == null || $name == '') {
                continue;
            }

            $new_name = str_replace('_', ' ', $name);

            $detail = $this->importLocation($new_name);
            array_push($locations, $detail['data']);
        }

        $productUnitImport = $this->importProductUnit($data);
        $productCategoryImport = $this->importProductCategory($data);
        $productImport = $this->importProduct($data, $locations[0], $productUnitImport['data'], $productCategoryImport['data'], $data['harga_jual_ecer']);

        $orderTypeEcerImport = $this->importOrderType('Ecer');
        // $orderTypeGrosirImport = $this->importOrderType('Grosir');
        $orderTypeImports = [$orderTypeEcerImport];

        foreach ($orderTypeImports as $orderTypeImport)
        {
            $orderTypeName = strtolower($orderTypeImport['data']->name);
            $hargaJual = $data["harga_jual_$orderTypeName"];

            $this->importServiceDetail($rowIndex, $data, $orderTypeImport, $productImport, $productUnitImport, $productCategoryImport, $hargaJual);
        }

        foreach ($locations as $location)
        {
            $product =  $productImport['data'];
            $productUnit = $productUnitImport['data'];

            foreach ($orderTypeImports as $orderTypeImport)
            {
                $orderType = $orderTypeImport['data'];
                $orderTypeName = strtolower($orderType->name);
                $hargaJual = $data["harga_jual_$orderTypeName"];

                $this->importProductSellPrice($location, $product, $productUnit, $orderType, $hargaJual);
            }
        }
        
        foreach ($locationNames as $name => $stock)
        {
            $location = null;
            $new_name = strtolower(str_replace('_', ' ', $name));
            foreach ($locations as $searchLocation)
            {
                if (strtolower($searchLocation->name) == $new_name) {
                    $location = $searchLocation;
                    break;
                }
            }

            if ($location == null)
            {
                continue;
            }

            $this->importStockMovement($data, $location, $product, $productUnit, $stock ?? 0);
            $this->importProductLocation($location, $product);
        }
    }

    public function chunkSize(): int
    {
        return 50;
    }

    private function importServiceDetail($rowIndex, array $data, array $orderTypeImport, array $productImport, array $productUnitImport, array $productCategoryImport, ?int $selling_price)
    {
        $importDetail = new ProductImportServiceDetail();
        $importDetail->fill($this->getParams($data));
        $importDetail->product_import_service_id = $this->importService->id;
        $importDetail->imported_line_row = $rowIndex;
        $importDetail->buying_price = $data['harga_pokok'];
        $importDetail->selling_price = $selling_price;
        $importDetail->location_id = 0;
        $importDetail->stok = 0;

        $orderType = $orderTypeImport['data'];
        $product = $productImport['data'];
        $productUnit = $productUnitImport['data'];
        $productCategory = $productCategoryImport['data'];

        $importDetail->order_type_id = $orderType->id;
        $importDetail->order_type_name = $orderType->name;
        $importDetail->order_type_created = $orderTypeImport['created'];
    
        $importDetail->product_id = $product->id;
        $importDetail->product_code = $product->code;
        $importDetail->product_name = $product->name;
        $importDetail->product_barcode = $product->barcode;
        $importDetail->product_created = $productImport['created'];

        $importDetail->product_unit_id = $productUnit->id;
        $importDetail->product_unit_name = $productUnit->name;
        $importDetail->product_unit_created = $productUnitImport['created'];

        $importDetail->product_category_id = $productCategory->id;
        $importDetail->product_category_name = $productCategory->name;
        $importDetail->product_category_created = $productCategoryImport['created'];

        $importDetail->save();
    }

    private function importLocation(string $locationName): array
    {
        $created = false;
        $search_name = UniqueCodeGenerator::generateSearchName($locationName);
        $data = $this->getLocationByName($search_name);
        if (!$data) {
            $created = true;
            $data = new Location();
            $data->entity_id = $this->entity->id;
            $data->code = UniqueCodeGenerator::generateCode();
            $data->name = $locationName;
            $data->search_name = strtolower($data->name);
            $data->timezone = $this->entity->timezone;
            $data->save();
        };

        return [
            'data' => $data,
            'created' => $created,
        ];
    }

    private function getLocationByName(string $name): ?Location
    {
        return Location::where('entity_id', $this->entity->id)->where('search_name', $name)->first();
    }

    private function importProductUnit(array $row): array
    {
        $created = false;
        $name = $row['satuan'];
        $search_name = UniqueCodeGenerator::generateSearchName($name);
        $productUnit = $this->getProductUnitByName($search_name);
        if (!$productUnit) {
            $created = true;
            $productUnit = new ProductUnit();
            $productUnit->entity_id = $this->entity->id;
            $productUnit->name = $name;
            $productUnit->search_name = $search_name;
            $productUnit->save();
        };

        return [
            'data' => $productUnit,
            'created' => $created,
        ];
    }

    private function getProductUnitByName(string $name): ?ProductUnit
    {
        return ProductUnit::where('entity_id', $this->entity->id)->where('search_name', $name)->first();
    }

    private function importProductCategory(array $row): array
    {
        $created = false;
        $name = $row['kategori'];
        $search_name = UniqueCodeGenerator::generateSearchName($name);
        $data = $this->getProductCategoryByName($search_name);
        if (!$data) {
            $created = true;
            $data = new ProductCategory();
            $data->entity_id = $this->entity->id;
            $data->name = $name;
            $data->search_name = $search_name;
            $data->save();
        };

        return [
            'data' => $data,
            'created' => $created,
        ];
    }

    private function getProductCategoryByName(string $name): ?ProductCategory
    {
        return ProductCategory::where('entity_id', $this->entity->id)->where('search_name', $name)->first();
    }

    private function importOrderType(string $name): array
    {
        $created = false;
        $search_name = UniqueCodeGenerator::generateSearchName($name);
        $data = $this->getOrderTypeByName($search_name);
        if (!$data) {
            $created = true;
            $data = new OrderType();
            $data->entity_id = $this->entity->id;
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

    private function getOrderTypeByName(string $name): ?OrderType
    {
        return OrderType::where('entity_id', $this->entity->id)->where('search_name', $name)->first();
    }

    private function importProduct(array $row, Location $location, ProductUnit $productUnit, ProductCategory $productCategory, ?int $sellPrice): array
    {
        $created = false;
        $product = $this->findProductByBarcode($row['barcode']);

        try {
            if (!$product) {
                $created = true;
                $product = new Product();
                $product->entity_id = $this->entity->id;
                $product->cost_of_goods_sold = $row['harga_pokok'] ?? 0;
            }
    
            $product->code = $row['kode'];
            $product->name = $row['nama'];
            $product->description = '';
            $product->sku = $row['sku'] ?? $row['barcode'] ?? UniqueCodeGenerator::generateCode();
            $product->barcode = $row['barcode'] ?? $row['sku'] ?? UniqueCodeGenerator::generateCode();
            $product->product_unit_id = $productUnit->id;
            $product->product_sell_unit_id = $productUnit->id;
            $product->product_category_id = $productCategory->id;
            $product->location_id = $location->id;
            $product->sell_price = $sellPrice ?? 0;
            $product->last_buying_price = $row['harga_pokok'] ?? 0;

            $product->save();
        } catch (Exception $e) {
            Log::error($e->getMessage());

            throw $e;
        }

        return [
            'data' => $product,
            'created' => $created,
        ];
    }

    private function findProductByCode(string $kode): ?Product
    {
        return Product::where('entity_id', $this->entity->id)->where('code', $kode)->first();
    }

    private function findProductByBarcode(string $kode): ?Product
    {
        return Product::where('entity_id', $this->entity->id)->where('barcode', $kode)->first();
    }

    private function importProductLocation(Location $location, Product $product)
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

    private function importProductSellPrice(Location $location, Product $product, ProductUnit $productUnit, OrderType $orderType, int $sellPrice)
    {
        $productLocationStock = $product->productSellPrices()->firstOrNew([
            'location_id' => $location->id,
            'order_type_id' => $orderType->id,
            'product_unit_id' => $productUnit->id,
        ]);

        $productLocationStock->sell_price = $sellPrice ?? 0;
        $productLocationStock->save();
    }

    private function importStockMovement(array $row, Location $location, Product $product, ProductUnit $productUnit, int $stock)
    {
        $data = new ProductStockMovement();

        $data->product_id = $product->id;
        $data->location_id = $location->id;
        $data->product_unit_id = $productUnit->id;

        $data->original_product_unit_id = $productUnit->id;

        $data->resource_id = $this->importService->id;
        $data->resource_type = $this->importService::class;

        $data->original_stock_out = 0;
        $data->original_stock_in = $stock ?? 0;
        $data->original_buying_price = $product->cost_of_goods_sold;
        $data->conversion_stock = 1; # should find conversion, not for now

        $data->stock_in = $data->original_stock_in * $data->conversion_stock;
        $data->stock_out = $data->original_stock_out * $data->conversion_stock;
        $data->buying_price = $data->original_buying_price * $data->conversion_stock;

        $data->save();
    }

    private function getParams($row): array
    {
        return [
            'product_import_service_id' => $this->importService->id,
            'kode' => $row['kode'],
            'nama' => $row['nama'],
            'deskripsi' => $row['deskripsi'],
            'satuan' => $row['satuan'],
            'berat' => $row['berat'] ?? 0,
            'harga_pokok' => $row['harga_pokok'],
            'harga_jual_ecer' => $row['harga_jual_ecer'],
            'harga_jual_grosir' => $row['harga_jual_grosir'],
            'kategori' => $row['kategori'],
            'stok_minimum' => $row['stok_minimum'],
            'barcode' => $row['barcode'] ?? UniqueCodeGenerator::generateCode(),
            // 'nama_lokasi' => $row['nama_lokasi'],
            // 'stok' => $row['stok'] ?? 0,
        ];
    }
}
