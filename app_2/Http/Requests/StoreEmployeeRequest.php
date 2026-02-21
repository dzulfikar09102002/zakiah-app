<?php

namespace App\Http\Requests;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;

class StoreEmployeeRequest extends BaseRequest
{
    protected $page = PageNameConstants::EmployeeMenu;
    protected $action = ActionConstants::StoreAction;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
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
