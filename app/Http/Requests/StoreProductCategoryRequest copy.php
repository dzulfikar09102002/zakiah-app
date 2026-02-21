<?php

namespace App\Http\Requests;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;

class StoreProductCategoryRequest extends BaseRequest
{
    protected $page = PageNameConstants::ProductCategoryMenu;
    protected $action = ActionConstants::StoreAction;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "parent_id" => 'nullable',
            "name" => 'required',
        ];
    }
}
