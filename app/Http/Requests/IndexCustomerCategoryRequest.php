<?php

namespace App\Http\Requests;

use App\Helpers\Constants\PageNameConstants;

class IndexCustomerCategoryRequest extends BaseIndexRequest
{
    protected $page = PageNameConstants::CustomerCategoryMenu;
    
    public function rules(): array
    {
        return [
            //
        ];
    }
}
