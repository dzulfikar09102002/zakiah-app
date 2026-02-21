<?php

namespace App\Helpers\Data\CustomerOrder;

use App\Models\Product;
use Illuminate\Support\Collection;

class ProductMap
{
    /** @var Product[] */
    private Collection $products;

    private array $productMapped;

    /**
     * Create a new class instance.
     * 
     * @param Product[] $products
     * 
     */
    public function __construct(Collection $products)
    {
        //
        $this->products = $products;
        $this->productMapped = array();
    }

    public function build()
    {
        $this->productMapped = [];
        foreach ($this->products as $product)
        {
            $this->productMapped[$product->id] = $product;
        }

        return $this;
    }

    public function get($id): ?Product
    {
        if (!array_key_exists($id, $this->productMapped)) {
            return null;
        }

        return $this->productMapped[$id];
    }
}
