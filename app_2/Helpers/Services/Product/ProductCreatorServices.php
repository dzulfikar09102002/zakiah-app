<?php

namespace App\Helpers\Services\Product;

use App\Helpers\Data\Product\ProductRequest;
use App\Models\Product;
use App\Models\ProductLocationStock;
use App\Models\ProductStockMovement;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ProductCreatorServices
{
    protected ProductRequest $request;
    protected ?int $productUnitId;
    protected Collection $locationIds;
    protected array $stockMovements;

    /**
     * Create a new class instance.
     */
    public function __construct(ProductRequest $request)
    {
        //
        $this->request = $request;
        $this->productUnitId = $this->request->getProductUnit()?->id;
        $this->locationIds = $this->request->getLocationIds();
        $this->stockMovements = $this->request->getStockMovements();
    }

    public function create(): Product
    {
        //
        $this->validateLocation();

        $product = new Product();
        $product->code = $this->request->getCode();
        $product->entity_id = $this->request->getEntity()->id;
        $product->created_by = $this->request->getCreatedBy()->id;
        $product->updated_by = $this->request->getUpdatedBy()->id;
        $product->cost_of_goods_sold = $this->request->getCostFfGoodsSold();
        $product->fill($this->request->getFillable());
        $product->save();

        $this->createProductLocationsStock($product);
        $this->createProductLocations($product);
        $this->createProductSellPrices($product);
        $this->createStockMovement($product);

        return $product;
    }

    protected function validateLocation()
    {
        if (count($this->locationIds) == 0) {
            throw ValidationException::withMessages([
                # change error message
                'location' => 'This value is incorrect',
            ]);
        }
    }

    protected function createStockMovement(Product $product) {
        foreach ($this->stockMovements as $stockMovement)
        {
            if ($stockMovement['stock'] < 0) {
                continue;
            }

            # find product location stock
            $locationStock = $this->getExistingProductLocationStock($product->id, $stockMovement['location_id'], $this->productUnitId);
            $currentStock = 0;
            if ($locationStock != null) { # already exists
                $currentStock = $locationStock->stock;

                if ($currentStock < 0) {
                    $currentStock = 0;
                }
            }


            $data = new ProductStockMovement();

            $data->product_id = $product->id;
            $data->location_id = $stockMovement['location_id'];
            $data->product_unit_id = $this->productUnitId;
    
            $data->original_product_unit_id = $this->productUnitId;
    
            $data->resource_id = $product->id;
            $data->resource_type = $product::class;
    
            $data->original_stock_out = $currentStock;
            $data->original_stock_in = $stockMovement['stock'];
            $data->original_buying_price = $stockMovement['buying_price'];
            $data->conversion_stock = 1; # should find conversion, not for now
    
            $data->stock_in = $data->original_stock_in * $data->conversion_stock;
            $data->stock_out = $data->original_stock_out * $data->conversion_stock;
            $data->buying_price = $data->original_buying_price * $data->conversion_stock;
    
            $data->save();
        }
    }

    protected function createProductLocationsStock(Product $product) {
        $locationIds = $this->getLocationForProductLocationsStock();

        $idx = 0;
        $datas = array_fill(0, count($locationIds), []);
        foreach ($locationIds as $locationId)
        {
            $locationStock = $this->getExistingProductLocationStock($product->id, $locationId, $this->productUnitId);
            if ($locationStock != null) { # already exists
                continue;
            }
            
            $datas[$idx] = [
                'location_id' => $locationId,
                'product_unit_id' => $this->productUnitId,
                'stock' => 0,
            ];
            $idx++;
        }

        if ($idx > 0) {
            $product->productLocationStocks()->createMany($datas);
        }
    }

    protected function createProductLocations(Product $product) {
        $locationIds = $this->getLocationForProductLocations();

        $idx = 0;
        $datas = array_fill(0, count($locationIds), []);
        foreach ($locationIds as $locationId)
        {
            $datas[$idx] = [
                'location_id' => $locationId,
                'pos_favourite' => false,
                'available_stock_pos' => false,
            ];
            $idx++;
        }

        if ($idx > 0) {
            $product->productLocations()->createMany($datas);
        }
    }

    protected function createProductSellPrices(Product $product) {
        $locationIds = $this->getLocationForProductSellPrices();

        $taxId = $this->request->getTax()?->id;
        $idx = 0;
        $datas = array_fill(0, count($locationIds), []);
        foreach ($locationIds as $locationId)
        {
            $datas[$idx] = [
                'location_id' => $locationId,
                'order_type_id' => null,
                'product_unit_id' => $this->productUnitId,
                'tax_id' => $taxId,
                'tax_setting' => $this->request->getTaxSetting(),
                'sell_price' => $this->request->getSellPrice(),
            ];
            $idx++;
        }

        if ($idx > 0) {
            $product->productSellPrices()->createMany($datas);
        }
    }

    protected function getLocationForProductLocationsStock(): Collection
    {
        //
        return $this->locationIds;
    }

    protected function getLocationForProductLocations(): Collection
    {
        //
        return $this->locationIds;
    }

    protected function getLocationForProductSellPrices(): Collection
    {
        //
        return $this->locationIds;
    }

    protected function getExistingProductLocationStock($productId, $locationId, $productUnitId): ?ProductLocationStock
    {
        return ProductLocationStock::where('location_id', $locationId)
            ->where('product_id', $productId)
            ->where('product_unit_id', $productUnitId)
            ->first();
    }
}
