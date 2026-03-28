<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreemployeeRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'first_name' => strtoupper(trim($this->first_name)),
            'last_name'  => strtoupper(trim($this->last_name)),
        ]);
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email'),
            ],
            'first_name' => [
                'required',
                'max:255',
                Rule::unique('employees')
                    ->where(fn ($q) => $q->where('last_name', $this->last_name)),
            ],
            'last_name' => 'required|max:255',
            'select_all_location' => 'required|boolean',
            'role_id' => 'required|exists:roles,id',
            'locations' => 'required|array',
            'locations.*.location_id' => 'required|exists:locations,id',
            'locations.*.role_id' => 'required|exists:roles,id',
            'locations.*.entity_permission' => 'nullable|array',
            'locations.*.location_permission' => 'nullable|array',
        ];
    }
}
