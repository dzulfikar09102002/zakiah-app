<?php

namespace App\Helpers\Queries;

class ReportWithProductFilterQuery extends ReportBaseQuery
{
    protected array $products, $excludeProducts;
    protected bool $selectAllProduct;

    /**
     * Create a new class instance.
     */
    public function __construct(array $params)
    {
        parent::__construct($params);

        $this->selectAllProduct = $this->params['select_all_product'] == 'true';
        if (array_key_exists('prods', $this->params)) {
            $this->products = $this->params['prods'];
        } else {
            $this->products = [];
        }

        if (array_key_exists('exclude_prods', $this->params)) {
            $this->excludeProducts = $this->params['exclude_prods'];
        } else {
            $this->excludeProducts = [];
        }
    }

    protected function buildWhereProduct($query) {
        if ($this->selectAllProduct && count($this->excludeProducts) > 0) {
            $query->whereNotIn('product_id', $this->excludeProducts);
        } else if ($this->selectAllProduct == false) {
            $query->whereIn('product_id', $this->products);
        }
    }
}
