<?php

namespace App\Helpers\Services\SaleTransaction;

use App\Models\Brand;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderDetail;
use App\Models\Location;
use App\Models\OrderType;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\SaleTransactionDetail;
use App\Models\Tax;

class DetailFieldSetter
{
    public static function location(array $mappedLocation, SaleTransactionDetail $saleTransactionDetail, CustomerOrder $customerOrder)
    {
        if (!array_key_exists($customerOrder->location_id, $mappedLocation)) {
            $mappedLocation[$customerOrder->location_id] = Location::find($customerOrder->location_id);
        }

        $saleTransactionDetail->location_id = $customerOrder->location_id;
        $saleTransactionDetail->location_name = $mappedLocation[$customerOrder->location_id]->name;
    }

    public static function brand(array $mappedBrand, SaleTransactionDetail $saleTransactionDetail, CustomerOrderDetail $customerOrderDetail)
    {
        if (!array_key_exists($customerOrderDetail->brand_id, $mappedBrand)) {
            $mappedBrand[$customerOrderDetail->brand_id] = Brand::find($customerOrderDetail->brand_id);
        }
        $saleTransactionDetail->brand_id = $customerOrderDetail->brand_id;
        $saleTransactionDetail->brand_name = $mappedBrand[$customerOrderDetail->brand_id]?->name;
    }

    public static function orderType(array $mappedOrderType, SaleTransactionDetail $saleTransactionDetail, CustomerOrderDetail $customerOrderDetail)
    {
        if (!array_key_exists($customerOrderDetail->order_type_id, $mappedOrderType)) {
            $mappedOrderType[$customerOrderDetail->order_type_id] = OrderType::find($customerOrderDetail->order_type_id);
        }

        $saleTransactionDetail->order_type_id = $customerOrderDetail->order_type_id;
        $saleTransactionDetail->order_type_name = $mappedOrderType[$customerOrderDetail->order_type_id]->name;
    }

    public static function product(array $mappedProduct, SaleTransactionDetail $saleTransactionDetail, CustomerOrderDetail $customerOrderDetail)
    {
        if (!array_key_exists($customerOrderDetail->product_id, $mappedProduct)) {
            $mappedProduct[$customerOrderDetail->product_id] = Product::find($customerOrderDetail->product_id);
        }
        $foundProduct = $mappedProduct[$customerOrderDetail->product_id];

        $saleTransactionDetail->product_id = $foundProduct->id;
        $saleTransactionDetail->product_name = $foundProduct->name;
        $saleTransactionDetail->product_sku = $foundProduct->sku;
        $saleTransactionDetail->product_code = $foundProduct->code;
        $saleTransactionDetail->product_description = $foundProduct->description;
        $saleTransactionDetail->cost_of_goods_sold = $foundProduct->cost_of_goods_sold;
    }

    public static function productCategory(array $mappedProductCategory, SaleTransactionDetail $saleTransactionDetail, CustomerOrderDetail $customerOrderDetail)
    {
        if (!array_key_exists($customerOrderDetail->product_category_id, $mappedProductCategory)) {
            $mappedProductCategory[$customerOrderDetail->product_category_id] = ProductCategory::find($customerOrderDetail->product_category_id);
        }
        $found = $mappedProductCategory[$customerOrderDetail->product_category_id];

        $saleTransactionDetail->product_category_id = $found?->id;
        $saleTransactionDetail->product_category_name = $found?->name;
    }

    public static function productUnit(array $mappedProductUnit, SaleTransactionDetail $saleTransactionDetail, CustomerOrderDetail $customerOrderDetail)
    {
        if (!array_key_exists($customerOrderDetail->product_unit_id, $mappedProductUnit)) {
            $mappedProductUnit[$customerOrderDetail->product_unit_id] = ProductUnit::find($customerOrderDetail->product_unit_id);
        }
        $found = $mappedProductUnit[$customerOrderDetail->product_unit_id];

        $saleTransactionDetail->product_unit_id = $found->id;
        $saleTransactionDetail->product_unit_name = $found->name;
    }

    public static function tax(array $mappedTax, SaleTransactionDetail $saleTransactionDetail, CustomerOrderDetail $customerOrderDetail)
    {
        if (!array_key_exists($customerOrderDetail->tax_id, $mappedTax)) {
            $mappedTax[$customerOrderDetail->tax_id] = Tax::find($customerOrderDetail->tax_id);
        }
        $found = $mappedTax[$customerOrderDetail->tax_id];

        $saleTransactionDetail->tax_id = $found?->id;
        $saleTransactionDetail->tax_name = $found?->name;
        $saleTransactionDetail->tax_rate = $customerOrderDetail->tax_rate;
        $saleTransactionDetail->tax_setting = $customerOrderDetail->tax_setting;
    }
}
