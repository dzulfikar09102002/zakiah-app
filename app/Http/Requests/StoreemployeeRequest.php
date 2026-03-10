<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreemployeeRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'first_name' => 'required|max:255',
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
