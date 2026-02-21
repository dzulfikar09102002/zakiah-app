<?php

namespace App\Http\Requests\Kasir;

use Illuminate\Foundation\Http\FormRequest;

class IndexKasirSaleTransactionRequest extends FormRequest
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
            'limit' => 'required|integer|min:0',
            'cursor' => 'nullable',
            'keyword' => 'nullable',
            'cashier_ids' => 'nullable|array',
            'only_logged_cashier' => 'nullable|in:true,false',
            'exclude_ids' => 'nullable|array',
            'refund_amount' => 'nullable',
            'locs' => 'required|array',
            'locs.*' => 'required|integer|min:0',
            'order_types' => 'nullable|array',
        ];
    }
}
