<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
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
    $roleId = $this->route('role')->id;

    return [
        'name' => [
            'required',
            'string',
            'max:100',
            Rule::unique('roles', 'name')->ignore($roleId),
        ],

        'parent_id' => ['required', 'exists:roles,id'],
        'entity_permission' => ['nullable', 'array'],
        'location_permission' => ['nullable', 'array'],
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
