<?php

namespace App\Helpers;

use App\Models\Product;

class ProductSellPriceFinder
{

    /**
     * @param ProductSellPrice[] $productPrices
     */
    public static function findProductSellPriceFromParam(Product $product, array $productPrices, array $line): int
    {
        if (array_key_exists('loyalty_id', $line) && array_key_exists('loyalty_reward_product_id', $line)) {
            return 0;
        }

        // if (array_key_exists('custom_price', $line) && $line['custom_price'] == true) {
        //     return $line['sell_price'];
        // }

        // $key = $line['product_id'] . $line['order_type_id'] . $line['product_unit_id'];
        // if (array_key_exists($key, $productPrices)) {
        //     return $productPrices[$key]->sell_price;
        // }

        return $product->sell_price;
    }
}
