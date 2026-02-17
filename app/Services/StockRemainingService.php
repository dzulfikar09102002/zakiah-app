<?php

namespace App\Services;

use App\Models\Location;
use App\Models\ProductCategory;
use App\Models\ProductLocationStock;

class StockRemainingService{
    public function getRemainingStock()
    {
        $locationId = request('location_id', 'all');
        $entityId = auth()->user()?->entity?->id;
        $product_category_id = request('product_category_id', 'all');
        $query = ProductLocationStock::query()
            ->with(['product:id,name', 'location:id,name'])

            ->whereHas('product', function ($q) use ($entityId) {
                $q->where('entity_id', $entityId);
            })

            ->whereHas('location', function ($q) use ($entityId) {
                $q->where('entity_id', $entityId);
            });

        if ($locationId !== 'all') {
            $query->where('location_id', $locationId);
        }
        if($product_category_id !== 'all'){
            $query->where('product_category_id', $product_category_id);
        }

        return $query
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function getLocations(){
        return Location::where('entity_id', auth()->user()?->entity?->id)->get()->map(function ($locatiuon) {
            return [
                'value' => $locatiuon->id,
                'label' => $locatiuon->name,
            ];
        });
    }

    public function getCategories()
    {
        return ProductCategory::where('entity_id', auth()->user()?->entity?->id)->get()->map(function ($category) {
            return [
                'value' => $category->id,
                'label' => $category->name,
            ];
        });
    }
}