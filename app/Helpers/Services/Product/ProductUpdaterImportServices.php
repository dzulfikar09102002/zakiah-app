<?php

namespace App\Helpers\Services\Product;

use App\Helpers\Data\Product\ProductRequest;
use App\Models\Product;
use App\Models\ProductImportService;
use App\Models\ProductLocation;
use App\Models\ProductLocationStock;
use App\Models\ProductSellPrice;
use App\Models\ProductStockMovement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductUpdaterImportServices extends ProductCreatorImportServices
{
    private Product $product;

    /**
     * Create a new class instance.
     */
    public function __construct(ProductRequest $request, Product $product)
    {
        //
        parent::__construct($request);

        $this->product = $product;
        $this->productUnitId = $this->productUnitId ?? $this->product->product_unit_id;
    }

    public function update(): Product
    {
        //
        $this->validateLocation();

        $this->updateCostOfGoodsSolds();
        $this->product->fill($this->request->getFillable());
        $this->product->save();

        // $this->deleteProductLocationsStock();
        $this->deleteProductSellPrices();
        $this->deleteProductLocations();

        $this->createProductLocationsStock($this->product);
        $this->createProductLocations($this->product);
        $this->createProductSellPrices($this->product);
        $this->createStockMovement($this->product);

        return $this->product;
    }

    private function updateCostOfGoodsSolds()
    {
        $cogs = ProductStockMovement::where('product_id', $this->product->id)
            ->whereIn('resource_type', [ProductImportService::class, Product::class])
            ->whereRaw('stock_in > stock_out')
            ->select(DB::raw('SUM(buying_price * stock_in) / SUM(stock_in) AS cogs'))
            ->first();

        if (!$cogs) {
            return;
        }

        if (!$cogs['cogs']) {
            $this->product->cost_of_goods_sold = $this->request->getCostFfGoodsSold();
            return;
        }

        $this->product->cost_of_goods_sold = $cogs['cogs'];
    }

    private function deleteProductLocationsStock()
    {
        //
        $datas = ProductLocationStock::where("product_id", $this->product->id)->whereNotIn('location_id', $this->locationIds)->where('product_unit_id', $this->productUnitId)->get();
        foreach ($datas as $data) {
            $data->delete();
        }
    }

    private function deleteProductLocations()
    {
        //
        $datas = ProductLocation::where("product_id", $this->product->id)->whereNotIn('location_id', $this->locationIds)->get();
        foreach ($datas as $data) {
            $data->delete();
        }
    }

    private function deleteProductSellPrices()
    {
        //
        $datas = ProductSellPrice::where("product_id", $this->product->id)
            ->whereNotIn('location_id', $this->locationIds)
            ->where('product_unit_id', $this->productUnitId)
            ->where('order_type_id', null)
            ->get();

        foreach ($datas as $data) {
            $data->delete();
        }
    }

    protected function getLocationForProductLocationsStock(): Collection
    {
        //
        return $this->product
            ->productLocationStocks()
            ->whereNotIn('location_id', $this->locationIds)
            ->where('product_unit_id', $this->productUnitId)
            ->select(['location_id'])->distinct()->pluck('location_id');
    }

    protected function getLocationForProductLocations(): Collection
    {
        //
        return $this->product
            ->productLocations()
            ->whereNotIn('location_id', $this->locationIds)
            ->select(['location_id'])->distinct()->pluck('location_id');
    }

    protected function getLocationForProductSellPrices(): Collection
    {
        //
        return $this->product
            ->productSellPrices()
            ->whereNotIn('location_id', $this->locationIds)
            ->where('product_unit_id', $this->productUnitId)
            ->where('order_type_id', null)
            ->select(['location_id'])->distinct()->pluck('location_id');
    }
}
