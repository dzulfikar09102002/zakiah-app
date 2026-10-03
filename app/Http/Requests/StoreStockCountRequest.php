<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi dokumen hitung stok (Stok Opname & Penyesuaian Stok).
 * Stok tercatat & selisih dihitung ulang di server.
 */
class StoreStockCountRequest extends FormRequest
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
            'location_id' => [
                Rule::requiredIf($this->isMethod('post')),
                Rule::exists('locations', 'id')->where('entity_id', $entityId),
            ],
            'note' => 'nullable|string',
            'auto_approve' => 'boolean',
            'products' => 'required|array|min:1',
            'products.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('products', 'id')->where('entity_id', $entityId),
            ],
            'products.*.counted_stock' => 'required|integer|min:0',
            'products.*.note' => 'nullable|string',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'location_id' => 'lokasi',
            'products' => 'produk',
            'products.*.product_id' => 'produk',
            'products.*.counted_stock' => 'stok fisik',
        ];
    }
}
