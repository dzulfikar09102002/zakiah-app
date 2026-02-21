<?php

namespace App\Http\Requests;

use App\Helpers\Constants\PageNameConstants;

class ShowBrandRequest extends BaseShowRequest
{
    protected $page = PageNameConstants::BrandMenu;

    public function rules(): array
    {
        return [
            //
        ];
    }
}
