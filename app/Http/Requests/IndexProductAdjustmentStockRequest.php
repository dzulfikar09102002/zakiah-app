<?php

namespace App\Http\Requests;

use App\Helpers\Constants\PageNameConstants;

class IndexProductAdjustmentStockRequest extends BaseIndexRequest
{
    protected $page = PageNameConstants::ProductAdjustmentStockMenu;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'statuses' => 'required|array',
            'select_all_location' => 'nullable|in:true,false',
            'locs' => 'nullable|array',
            'exclude_locs' => 'nullable|array',
        ];
    }
}
