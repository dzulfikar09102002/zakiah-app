<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // penting!
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim($this->name),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')->where(function ($query) {
                    return $query->whereNotNull('parent_id');
                }),
            ],
            'entity_permission' => 'nullable|array',
            'location_permission' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama role tidak boleh kosong',
            'name.unique'   => 'Nama role sudah ada',
        ];
    }
}