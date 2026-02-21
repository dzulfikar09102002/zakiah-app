<?php

namespace App\Http\Requests;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;
use Illuminate\Foundation\Http\FormRequest;

class DestroyCustomerCategoryRequest extends BaseRequest
{
    protected $page = PageNameConstants::CustomerCategoryMenu;
    protected $action = ActionConstants::DestroyAction;
    
    public function rules(): array
    {
        return [
            //
        ];
    }
}
