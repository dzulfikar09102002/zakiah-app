<?php

namespace App\Http\Requests;

use App\Models\OrderType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var OrderType $orderType */
        $orderType = $this->route('orderType'); // atau $this->route('id')
        
        $rules = [
            "fixed_fee" => 'sometimes|required|integer|min:0',
            "variable_fee" => 'sometimes|required|integer|min:0',
            "require_customer_data" => 'nullable|boolean',
            "payment_method_id" => 'nullable|exists:payment_methods,id',
        ];

        if ($this->has('name')) {
            $rules['name'] = [
                'required',
                Rule::unique('order_types', 'name')
                    ->where('entity_id', auth()->user()->entity_id)
                    ->whereNull('deleted_at')
                    ->ignore($orderType?->id), 
            ];
        }

        return $rules;
    }
}