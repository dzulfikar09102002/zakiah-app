<?php

namespace App\Http\Requests;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;

class ActivateCustomerCategoryRequest extends BaseRequest
{
    protected $page = PageNameConstants::CustomerCategoryMenu;
    protected $action = ActionConstants::ActivateAction;
    
    public function rules(): array
    {
        return [
            //
        ];
    }
}
