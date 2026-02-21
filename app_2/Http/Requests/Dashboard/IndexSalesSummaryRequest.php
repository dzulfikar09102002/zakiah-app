<?php

namespace App\Http\Requests\Dashboard;

use App\Helpers\Constants\PageNameConstants;
use App\Http\Requests\BaseIndexRequest;

class IndexSalesSummaryRequest extends BaseIndexRequest
{
    protected $page = PageNameConstants::DashboardMenu;

    public function rules(): array
    {
        return [
            //
            'start_at' => 'required|date',
            'end_at' => 'required|date',
            'select_all_location' => 'required|in:true,false',
            'locs' => 'nullable|array',
            'exclude_locs' => 'nullable|array',
        ];
    }
}
