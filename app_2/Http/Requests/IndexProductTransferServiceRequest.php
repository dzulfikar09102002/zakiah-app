<?php

namespace App\Http\Requests;

use App\Helpers\Constants\PageNameConstants;

class IndexProductTransferServiceRequest extends BaseIndexRequest
{
    protected $page = PageNameConstants::ProductTransferMenu;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'from_locs' => 'nullable|array',
            'from_select_all_location' => 'nullable|in:true,false',
            'from_exclude_locs' => 'nullable|array',
            'to_locs' => 'nullable|array',
            'to_select_all_location' => 'nullable|in:true,false',
            'to_exclude_locs' => 'nullable|array',
            'statuses' => 'nullable|array',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ];
    }
}
