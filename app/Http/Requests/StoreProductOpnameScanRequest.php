<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductOpnameScanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'uuid' => ['required', 'uuid'],
            'barcode' => ['nullable', 'string', 'max:255', 'required_without:product_id'],
            'product_id' => ['nullable', 'integer'],
            // Satu scan selalu satu barang, jumlah tidak boleh ditentukan dari perangkat.
            'qty' => ['prohibited'],
        ];
    }
}
