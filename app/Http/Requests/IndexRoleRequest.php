<?php

namespace App\Http\Requests;

use App\Helpers\Constants\PageNameConstants;

class IndexRoleRequest extends BaseIndexRequest
{
    protected $page = PageNameConstants::RoleMenu;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'limit' => 'nullable',
            'keyword' => 'nullable',
            'parent_ids' => 'nullable|array',
            'show_system' => 'nullable|in:true,false',
        ];
    }
}
