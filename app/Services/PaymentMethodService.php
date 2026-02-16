<?php

namespace App\Services;

use App\Models\PaymentMethod;

class PaymentMethodService{
    public function getPaymentMethod()
    {
        $search = request('search', '');

        return PaymentMethod::where('entity_id', auth()->user()?->entity?->id)
            ->whereLike('name', "%$search%")
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }
}