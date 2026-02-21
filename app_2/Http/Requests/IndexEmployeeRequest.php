<?php

namespace App\Http\Requests;

use App\Helpers\Constants\PageNameConstants;

class IndexEmployeeRequest extends BaseIndexRequest
{
    protected $page = PageNameConstants::EmployeeMenu;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'keyword' => 'nullable',
            'roles' => 'nullable|array',
            'selected_ids' => 'nullable|array',
            'exclude_ids' => 'nullable|array',
            'locs' => 'nullable|array',
        ];
    }
}
