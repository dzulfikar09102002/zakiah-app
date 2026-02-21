<?php

namespace App\Http\Requests\Kasir;

use Illuminate\Foundation\Http\FormRequest;

class PayKasirCustomerOrderRequest extends FormRequest
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
            //
            "payments" => 'required|array',
            "payments.*.payment_method_id" => 'required|integer|exists:payment_methods,id',
            "payments.*.amount_receive" => 'required|integer|min:-1',
            "payments.*.change" => 'required|integer|min:-1',
            "payments.*.card_detail" => 'nullable|array',
        ];
    }
}
