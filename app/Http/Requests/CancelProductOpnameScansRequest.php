<?php

namespace App\Http\Requests;

use App\Services\ProductOpnameScanService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CancelProductOpnameScansRequest extends FormRequest
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
            'reason' => ['required', 'string', 'min:3', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'qty.required' => 'Jumlah yang dibatalkan wajib diisi.',
            'qty.integer' => 'Jumlah yang dibatalkan harus berupa angka.',
            'qty.min' => 'Jumlah yang dibatalkan minimal 1.',
            'qty.max' => 'Jumlah yang dibatalkan maksimal '.ProductOpnameScanService::MAX_MANUAL_QTY.' sekali simpan.',
            'reason.required' => 'Alasan pembatalan wajib diisi.',
            'reason.min' => 'Alasan pembatalan terlalu pendek.',
        ];
    }
}
