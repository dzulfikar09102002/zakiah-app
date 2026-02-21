<?php

namespace App\Http\Requests;

use App\Helpers\Constants\PageNameConstants;

class IndexCustomerRequest extends BaseIndexRequest
{
    protected $page = PageNameConstants::CustomerMenu;

    public function rules(): array
    {
        return [
            //
            'keyword' => 'nullable',
            'phone_number' => 'nullable',
        ];
    }
}
