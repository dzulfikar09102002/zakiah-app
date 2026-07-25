<?php

namespace App\Helpers\Services\Product;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductTransformerFromRequestForUpdateServices extends ProductTransformerFromRequestServices
{
    private Product $product;

    /**
     * Create a new class instance.
     */
    public function __construct(Request $request, Product $product)
    {
        //
        parent::__construct($request);

        $this->product = $product;
        $this->fillParamsWithProduct();
    }

    private function fillParamsWithProduct()
    {
        $fields = [
            'sku', 'barcode',
            'select_all_location', 'location_ids', 'exclude_location_ids', 'sell_price', 'tax_id', 'tax_setting',
            'location_id', 'product_unit_id', 'product_sell_unit_id', 'supplier_id', 
            'supplier_name'
        ];

        foreach ($fields as $field) {
            if (!array_key_exists($field, $this->params)) {
                $this->params[$field] = $this->product[$field];
            }
        }
    }
}
