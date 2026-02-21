<?php

namespace App\Services;
use App\Models\ProductUnit;

class ProductUnitService
{
    public function getUnits()
    {
        $search = request('search', '');

        return ProductUnit::query()
            ->where('entity_id', auth()->user()?->entity?->id)
            ->withTrashed()

            ->when($search, fn ($query) =>
                $query->whereLike('name', "%{$search}%")
            )

            ->when(request('statuses'), fn ($query, $statuses) =>
                $query->whereIn('status', (array) $statuses)
            )

            ->paginate(request('per_page', 10))
            ->withQueryString();
    }
}