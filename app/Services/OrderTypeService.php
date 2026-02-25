<?php

namespace App\Services;

use App\Enums\StatusEnum;
use App\Helpers\UniqueCodeGenerator;
use App\Models\OrderType;
use App\Models\PaymentMethod;
class OrderTypeService{
    public function getOrderTypes()
    {
        $search = request('search', '');
        return OrderType::with('paymentMethod')->where('entity_id', auth()->user()?->entity?->id)
        ->whereLike('name', "%$search%")
        ->paginate(request('per_page', 10))
        ->withQueryString();
    }
    public function getDeletedOrderTypes()
    {
        $search = request('search', '');
        
        return OrderType::onlyTrashed()
            ->with('paymentMethod')
            ->where('entity_id', auth()->user()?->entity?->id)
            ->whereLike('name', "%$search%")
            ->paginate(request('per_page', 10))
            ->withQueryString();
    }
    public function getPaymentMethods()
    {
        return PaymentMethod::where('entity_id', auth()->user()?->entity?->id)->get();
    }

    public function store(array $data)
    {
        $user = auth()->user();

        return OrderType::create([
            'name' => $data['name'],
            'payment_method_id' => $data['payment_method_id'],
            'fixed_fee' => $data['fixed_fee'] ?? 0,
            'variable_fee' => $data['variable_fee'] ?? 0,
            'require_customer_data' => $data['require_customer_data'] ?? true,
            'search_name' => UniqueCodeGenerator::generateSearchName($data['name']),
            'status' => StatusEnum::Active,
            'entity_id' => $user->entity?->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
    }

    public function update(OrderType $orderType, array $data)
    {
        return $orderType->update([
            'name' => $data['name'],
            'payment_method_id' => $data['payment_method_id'],
            'fixed_fee' => $data['fixed_fee'] ?? 0,
            'variable_fee' => $data['variable_fee'] ?? 0,
            'require_customer_data' => $data['require_customer_data'] ?? true,
            'search_name' => UniqueCodeGenerator::generateSearchName($data['name']),
            'updated_by' => auth()->user()->id,
        ]);
    }
    public function delete(OrderType $orderType)
    {
        return $orderType->delete();
    }

    public function restore(int $id)
    {
        $orderType = OrderType::withTrashed()->findOrFail($id);
        return $orderType->restore();
    }
}