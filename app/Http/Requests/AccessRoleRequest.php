<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Nama dan keterangan role. Centangan Hak Akses disimpan terpisah
 * (AccessRolePermissionRequest).
 */
class AccessRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name'))]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'max:100',
                Rule::unique('access_roles', 'name')
                    ->where('entity_id', auth()->user()?->entity?->id)
                    ->whereNull('deleted_at')
                    ->ignore((int) $this->route('id')),
            ],
            'note' => ['nullable', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama role wajib diisi.',
            'name.unique' => 'Nama role ini sudah ada.',
        ];
    }
}
