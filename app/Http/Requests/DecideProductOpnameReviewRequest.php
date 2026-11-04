<?php

namespace App\Http\Requests;

use App\Enums\OpnameReviewDecision;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DecideProductOpnameReviewRequest extends FormRequest
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
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
