<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLoyaltyRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'miniminal_transaction_value' => 'required|integer|min:1',
            'reward_point' => 'required|integer|min:1',
            'allow_multiple' => 'boolean',
            'reward_products' => 'required|array|min:1',
            'reward_products.*.id' => 'nullable|integer',
            'reward_products.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('products', 'id')->where('entity_id', $entityId),
            ],
            'reward_products.*.product_unit_id' => 'required|integer|exists:product_units,id',
            'reward_products.*.point_needed' => 'required|integer|min:1',
            'reward_products.*.maximum_quantity' => 'nullable|integer|min:1',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nama loyalty',
            'miniminal_transaction_value' => 'minimal transaksi',
            'reward_point' => 'poin hadiah',
            'reward_products' => 'produk hadiah',
            'reward_products.*.product_id' => 'produk',
            'reward_products.*.product_unit_id' => 'satuan',
            'reward_products.*.point_needed' => 'poin dibutuhkan',
            'reward_products.*.maximum_quantity' => 'maksimal kuantitas',
        ];
    }
}
