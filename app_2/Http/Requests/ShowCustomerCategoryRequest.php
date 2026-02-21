<?php

namespace App\Http\Requests;

use App\Helpers\Constants\PageNameConstants;

class ShowCustomerCategoryRequest extends BaseShowRequest
{
    protected $page = PageNameConstants::CustomerCategoryMenu;

    public function rules(): array
    {
        return [
            //
        ];
    }
}
