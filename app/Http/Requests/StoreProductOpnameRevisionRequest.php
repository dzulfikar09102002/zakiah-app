<?php

namespace App\Http\Requests;

use App\Enums\OpnameRevisionReason;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductOpnameRevisionRequest extends FormRequest
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
            'product_id' => ['required', 'integer'],
            'adjustment_qty' => ['required', 'integer', 'between:-100000,100000'],
            'reason' => ['required', Rule::enum(OpnameRevisionReason::class)],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'product_id' => 'produk',
            'adjustment_qty' => 'jumlah penyesuaian',
            'reason' => 'alasan',
            'note' => 'catatan',
        ];
    }
}
