<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrderTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => [
            'required',
            Rule::unique('order_types', 'name')
                ->whereNull('deleted_at')
                ->ignore($this->route('id')), 
    ],
            "fixed_fee" => 'required|integer|min:0',
            "variable_fee" => 'required|integer|min:0',
            "require_customer_data" => 'nullable|boolean',
            "payment_method_id" => 'nullable|exists:payment_methods,id',
        ];
    }
}
