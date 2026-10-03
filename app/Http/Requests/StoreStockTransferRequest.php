<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockTransferRequest extends FormRequest
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
        $entityId = $this->user()?->entity?->id;

        return [
            'from_location_id' => ['required', 'integer', Rule::exists('locations', 'id')->where('entity_id', $entityId)],
            'to_location_id' => ['required', 'integer', 'different:from_location_id', Rule::exists('locations', 'id')->where('entity_id', $entityId)],
            'request_note' => 'nullable|string',
            'auto_approve' => 'boolean',
            'products' => 'required|array|min:1',
            'products.*.product_id' => ['required', 'integer', 'distinct', Rule::exists('products', 'id')->where('entity_id', $entityId)],
            'products.*.quantity' => 'required|integer|min:1',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'from_location_id' => 'lokasi asal',
            'to_location_id' => 'lokasi tujuan',
            'products' => 'produk',
            'products.*.product_id' => 'produk',
            'products.*.quantity' => 'jumlah',
        ];
    }
}
