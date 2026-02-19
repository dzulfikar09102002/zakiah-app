<?php

namespace App\Services;

use App\Models\PaymentMethod;
use DateTime;

class PaymentMethodService{
    public function getPaymentMethod()
    {
        $search = request('search', '');

        return PaymentMethod::where('entity_id', auth()->user()?->entity?->id)
            ->whereLike('name', "%$search%")
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }

    public function store(array $data)
    {
        $user = auth()->user();

        return PaymentMethod::create([
            'name' => $data['name'],
            'kind' => $data['kind'],
            'fixed_fee' => $data['fixed_fee'],
            'variable_fee' => $data['variable_fee'],
            'entity_id' => $user->entity?->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'created_at' => now(),
            'updated_at' => now()

        ]);
    }
}