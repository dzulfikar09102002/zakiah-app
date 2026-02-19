<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\PaymentMethod;

class StorePaymentMethodRequest extends FormRequest
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
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim($this->name),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'kind' => ['required', 'string', 'max:100'],
            'fixed_fee' => ['required', 'numeric', 'min:0'],
            'variable_fee' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {

            $normalizedInput = strtolower(str_replace(' ', '', $this->name));

            $exists = PaymentMethod::whereRaw(
                "LOWER(REPLACE(name, ' ', '')) = ?",
                [$normalizedInput]
            )->exists();

            if ($exists) {
                $validator->errors()->add(
                    'name',
                    'Nama metode pembayaran sudah digunakan.'
                );
            }
        });
    }
}
