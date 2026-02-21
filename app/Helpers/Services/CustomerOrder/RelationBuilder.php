<?php

namespace App\Helpers\Services\CustomerOrder;

use App\Helpers\Data\CustomerOrder\ProductMap;
use App\Models\Brand;
use App\Models\Entity;
use App\Models\Location;
use App\Models\OrderType;
use App\Models\Product;
use App\Models\ProductSellPrice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class RelationBuilder
{
    private array $lines;
    private OrderType $defaultOrderType;
    private Entity $entity;
    private Location $location;
    private array $productCategories, $productPrices, $brands, $orderTypes, $productUnits, $taxes;
    private ProductMap $products;
    
    /**
     * Create a new class instance.
     */
    public function __construct(array $lines, Entity $entity, Location $location, OrderType $defaultOrderType)
    {
        //
        $this->lines = $lines;
        $this->defaultOrderType = $defaultOrderType;
        $this->entity = $entity;
        $this->location = $location;

        $this->setProducts();
        $this->setProductCategories();
        $this->setProductPrices();
        $this->setBrands();
        $this->setOrderTypes();
        $this->setProductUnits();
        $this->setTaxes();
    }

    private function setProducts()
    {
        $lines = $this->lines;
        $productIds = array_unique(array_column($lines, 'product_id'));
        $products = $this->entity->products()->whereIn('id', $productIds)->get();

        if (count($productIds) != count($products))
        {
            throw ValidationException::withMessages([
                'product' => __('product.missing_product'),
            ]);
        }

        // $this->products = $this->groupDatas($products);
        $this->products = (new ProductMap($products))->build();
    }

    /**
     * @return ProductMap
     */
    public function getProducts(): ProductMap
    {
        return $this->products;
    }

    private function setProductCategories()
    {
        $lines = $this->lines;
        $ids = array_unique(array_column($lines, 'product_category_id'));
        $datas = $this->entity->productCategories()->whereIn('id', $ids)->get();

        $this->productCategories = $this->groupDatas($datas);
    }

    public function getProductCategories(): array
    {
        return $this->productCategories;
    }

    private function setProductPrices()
    {
        $lines = $this->lines;
        $productIds = array_unique(array_column($lines, 'product_id'));
        $locationId = $this->location->id;
        $orderTypeId = $this->defaultOrderType->id;

        $prices = ProductSellPrice::where('product_id', $productIds)
            ->where(function (Builder $query) use($locationId) {
                $query->orWhere('location_id', $locationId)->orWhere('location_id', null);
            })
            ->where(function (Builder $query) use($lines, $orderTypeId) {
                foreach ($lines as $line)
                {
                    $query->orWhere(function (Builder $query) use($line, $orderTypeId) {
                        $query
                            ->Where('product_id', $line['product_id'])
                            ->where(function (Builder $query) use($line, $orderTypeId) {
                                $query
                                    ->where('order_type_id', $line['order_type_id'] ?? $orderTypeId)
                                    ->orWhere('order_type_id', null);
                            });
                    });
                }
            })
            ->where(function (Builder $query) use($lines) {
                foreach ($lines as $line)
                {
                    $query->orWhere(function (Builder $query) use($line) {
                        $query
                            ->Where('product_id', $line['product_id'])
                            ->where(function (Builder $query) use($line) {
                                $query
                                    ->where('product_unit_id', $line['product_unit_id'] )
                                    ->orWhere('product_unit_id', null);
                            });
                    });
                }
            })
            ->get();

        $result = [];
        foreach ($lines as $line)
        {
            $key = $line['product_id'] . ($line['order_type_id'] ?? $orderTypeId) . $line['product_unit_id'];
            if (array_key_exists($key, $result))
            {
                continue;
            }

            foreach ($prices as $price)
            {
                if (
                    $price->product_id == $line['product_id'] && 
                    ($price->product_unit_id == null || $price->product_unit_id == $line['product_unit_id']) && 
                    ($price->order_type_id == null || $price->order_type_id == $line['order_type_id'])
                ) {
                    $result[$key] = $price;
                }
            }
        }

        $this->productPrices = $result;
    }

    public function getProductPrices(): array
    {
        return $this->productPrices;
    }

    private function setBrands()
    {
        $lines = $this->lines;
        $ids = array_unique(array_column($lines, 'brand_id'));
        $datas = Brand::whereIn('id', $ids)->get();

        // if (count($ids) != count($datas))
        // {
        //     throw ValidationException::withMessages([
        //         'product' => __('product.missing_brand'),
        //     ]);
        // }

        $this->brands = $this->groupDatas($datas);
    }

    public function getBrands(): array
    {
        return $this->brands;
    }

    private function setOrderTypes()
    {
        $lines = $this->lines;
        $ids = array_column($lines, 'order_type_id');
        $defaultOrderTypeId = $this->defaultOrderType->id;

        array_push($ids, $defaultOrderTypeId);

        $datas = $this->entity->orderTypes()->whereIn('id', $ids)->get();

        $this->orderTypes = $this->groupDatas($datas);
    }

    public function getOrderTypes(): array
    {
        return $this->orderTypes;
    }

    private function setProductUnits()
    {
        $lines = $this->lines;
        $ids = array_column($lines, 'product_unit_id');
        $datas = $this->entity->productUnits()->whereIn('id', $ids)->get();

        $this->productUnits = $this->groupDatas($datas);
    }

    public function getProductUnits(): array
    {
        return $this->productUnits;
    }

    private function setTaxes()
    {
        $datas = $this->entity->taxes()->get();

        $this->taxes = $this->groupDatas($datas);
    }

    public function getTaxes(): array
    {
        return $this->taxes;
    }

    public function getDefaultOrderTypeId(): int
    {
        return $this->defaultOrderType->id;
    }

    private function groupDatas(Collection $datas): array
    {
        $results = [];
        foreach ($datas as $data)
        {
            $results[$data->id] = $data;
        }

        return $results;
    }
}
