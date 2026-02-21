<?php

namespace App\Http\Requests\Report;

use App\Helpers\Constants\PageNameConstants;
use App\Http\Requests\BaseIndexRequest;

class IndexReportStockMovementRequest extends BaseIndexRequest
{
    protected $page = PageNameConstants::ReportStockMovementMenu;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'limit' => 'required|integer',
            'page' => 'required|integer',
            'start_at' => 'required|date',
            'end_at' => 'required|date',
            'select_all_location' => 'required|in:true,false',
            'locs' => 'nullable|array',
            'exclude_locs' => 'nullable|array',
            'select_all_product' => 'required|in:true,false',
            'prods' => 'nullable|array',
            'exclude_prods' => 'nullable|array',
        ];
    }
}
