<?php

namespace App\Http\Requests;

use App\Enums\OpnameReviewDecision;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LockProductOpnameReviewRequest extends FormRequest
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
            'decision' => ['required', Rule::enum(OpnameReviewDecision::class)],
            'note' => ['required', 'string', 'min:3', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'decision.required' => 'Pilih pakai stok SO atau stok sistem.',
            'note.required' => 'Alasan wajib diisi.',
            'note.min' => 'Alasan terlalu pendek, minimal 3 huruf.',
        ];
    }
}
