<?php

namespace App\Http\Requests;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;

class StoreTaxRequest extends BaseRequest
{
    protected $page = PageNameConstants::TaxMenu;
    protected $action = ActionConstants::StoreAction;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => 'required',
            "rate" => 'required|integer|min:0',
        ];
    }
}
