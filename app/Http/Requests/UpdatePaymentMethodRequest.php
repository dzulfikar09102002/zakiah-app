<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim($this->name),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_methods')->where(function ($query) {
                    return $query->where('entity_id', $this->entity_id)
                                 ->whereNull('deleted_at');
                }),
            ],
            'kind' => ['required', 'string', 'max:100'],
            'fixed_fee' => ['required', 'numeric', 'min:0'],
            'variable_fee' => ['required', 'numeric', 'min:0'],
            'entity_id' => ['required'],
        ];
    }
}
