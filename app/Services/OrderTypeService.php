<?php

namespace App\Services;

use App\Models\OrderType;
class OrderTypeService{
    public function getOrderTypes()
    {
        $search = request('search', '');
        OrderType::where('entity_id', auth()->user()?->entity?->id)
        ->whereLike('name', "%$search%")
        ->paginate(request('per_page', 10))
        ->withQueryString();
    }
}