<?php

namespace App\Http\Requests\Kasir;

use Illuminate\Foundation\Http\FormRequest;

class RefundKasirSaleTransactionRequest extends FormRequest
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
            'saleReferenceId' => 'required',
            'reason' => 'required',
            'notes' => 'required',
            'sale_transaction_details' => 'required',
            'sale_transaction_details.*.id' => 'required|integer',
            'sale_transaction_details.*.quantity' => 'required|integer',
            "payments" => 'required|array',
            "payments.*.payment_method_id" => 'required|integer|min:-1',
            "payments.*.amount_receive" => 'required|integer|min:-1',
            "payments.*.change" => 'required|integer|min:-1',
        ];
    }
}
