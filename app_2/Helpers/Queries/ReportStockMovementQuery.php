<?php

namespace App\Helpers\Queries;

use App\Models\ProductStockMovement;
use Illuminate\Support\Facades\DB;

class ReportStockMovementQuery extends ReportWithProductFilterQuery
{
    protected function generateBody(): array
    {
        $bodies = array();

        foreach ($this->getData() as $data) {
            array_push($bodies, [
                'product_name' => $data['product_name'],
                'location_name' => $data['location_name'],
                'product_unit_name' => $data['product_unit_name'],
                'created_at' => $data['created_at'],
                'stock_in' => $data['stock_in'],
                'stock_out' => $data['stock_out'],
                'sell_price' => $data['sell_price'],
            ]);
        }

        return $bodies;
    }

    private function baseQuery($query)
    {
        $query->join('products', 'products.id', '=', 'product_stock_movements.product_id')
            ->join('product_units', 'product_units.id', '=', 'product_stock_movements.product_unit_id')
            ->join('locations', 'locations.id', '=', 'product_stock_movements.location_id');

        return $this->buildQuery($query);
    }

    private function buildQuery($query) {
        $this->buildWhereLocation($query);
        $this->buildWhereProduct($query);
        $this->buildWhereSalesTime($query);

        return $query
            ->selectRaw("products.name as 'product_name'")
            ->selectRaw("locations.name as 'location_name'")
            ->selectRaw("product_units.name as 'product_unit_name'")
            ->selectRaw("DATE_FORMAT(product_stock_movements.created_at, '%Y-%m-%d') as 'created_at'")
            ->selectRaw("products.sell_price")
            ->groupBy(
                'products.name', 'locations.name', 'product_units.name',
                DB::raw("DATE_FORMAT(product_stock_movements.created_at, '%Y-%m-%d')"), 'products.sell_price'
            );
    }

    protected function buildTotal(): int
    {
        return ProductStockMovement::fromSub(function ($query) {
            $this->baseQuery($query->from('product_stock_movements'));
        }, 'a')->count('product_name');
    }

    private function getData()
    {
        $query = $this->baseQuery(ProductStockMovement::whereNotNull('resource_id'))
            ->selectRaw("sum(product_stock_movements.stock_in) as 'stock_in'")
            ->selectRaw("sum(product_stock_movements.stock_out) as 'stock_out'")
            ->orderBy('products.name')
            ->orderBy('locations.name')
            ->orderBy(DB::raw("DATE_FORMAT(product_stock_movements.created_at, '%Y-%m-%d')"));

       return $this->queryLimitOffset($query)->get();
    }

    private function buildWhereLocation($query) {
        $query->whereIn('product_stock_movements.location_id', $this->location_ids);
        
        if ($this->selectAllLocation && count($this->excludeLocations) > 0) {
            $query->whereNotIn('product_stock_movements.location_id', $this->excludeLocations);
        } else if ($this->selectAllLocation == false && count($this->locations) > 0) {
            $query->whereIn('product_stock_movements.location_id', $this->locations);
        }
    }

    private function buildWhereSalesTime($query)
    {
        $query
            ->where('product_stock_movements.created_at', '>=', $this->startAt)
            ->where('product_stock_movements.created_at', '<=', $this->endAt);
    }
}
