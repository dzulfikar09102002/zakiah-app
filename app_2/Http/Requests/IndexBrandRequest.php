<?php

namespace App\Http\Requests;

use App\Helpers\Constants\PageNameConstants;

class IndexBrandRequest extends BaseIndexRequest
{
    protected $page = PageNameConstants::BrandMenu;

    public function rules(): array
    {
        return [
            'search' => 'nullable',
            'code' => 'nullable',
            'statuses' => 'nullable|in:active,archived',
        ];
    }
}
