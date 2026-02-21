<?php

namespace App\Http\Requests;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;

class ArchiveCustomerCategoryRequest extends BaseRequest
{
    protected $page = PageNameConstants::CustomerCategoryMenu;
    protected $action = ActionConstants::ArchiveAction;
    
    public function rules(): array
    {
        return [
            //
        ];
    }
}
