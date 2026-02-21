<?php

namespace App\Http\Requests;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;

class StoreRoleRequest extends BaseRequest
{
    protected $page = PageNameConstants::RoleMenu;
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
            'parent_id' => 'required|exists:roles,id',
            'name' => 'required',
            'entity_permission' => 'nullable|array',
            'location_permission' => 'nullable|array',
        ];
    }
}
