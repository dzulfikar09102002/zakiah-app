<?php

namespace App\Http\Requests\Dashboard;

use App\Helpers\Constants\PageNameConstants;
use App\Http\Requests\BaseIndexRequest;

class IndexSalesAnnualSummaryRequest extends BaseIndexRequest
{
    protected $page = PageNameConstants::DashboardMenu;

    public function rules(): array
    {
        return [
            //
            'first_year' => 'required|numeric',
            'second_year' => 'required|numeric',
            'select_all_location' => 'required|in:true,false',
            'locs' => 'nullable|array',
            'exclude_locs' => 'nullable|array',
        ];
    }
}
