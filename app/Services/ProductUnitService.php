<?php

namespace App\Services;
use App\Models\ProductUnit;

class ProductUnitService
{
    public function getUnits()
    {
        $search = request('search', '');

        return ProductUnit::where('entity_id', auth()->user()?->entity?->id)
            ->withTrashed()
            ->whereLike('name', "%$search%")
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }
}