<?php

namespace App\Http\Requests;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;

class StockProductRequest extends BaseRequest
{
    protected $page = PageNameConstants::ProductMenu;
    protected $action = ActionConstants::IndexAction;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "product" => 'nullable',
            "product_unit" => 'nullable',
            "location" => 'nullable',
        ];
    }
}
