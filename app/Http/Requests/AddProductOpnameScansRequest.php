<?php

namespace App\Http\Requests;

use App\Services\ProductOpnameScanService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AddProductOpnameScansRequest extends FormRequest
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
            'qty' => ['required', 'integer', 'min:1', 'max:'.ProductOpnameScanService::MAX_MANUAL_QTY],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'qty.required' => 'Jumlah tambahan wajib diisi.',
            'qty.integer' => 'Jumlah tambahan harus berupa angka.',
            'qty.min' => 'Jumlah tambahan minimal 1.',
            'qty.max' => 'Jumlah tambahan maksimal '.ProductOpnameScanService::MAX_MANUAL_QTY.' sekali simpan.',
        ];
    }
}
