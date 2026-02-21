<?php

namespace App\Http\Requests;

use App\Helpers\Constants\PageNameConstants;

class ShowProductCategoryRequest extends BaseShowRequest
{
    protected $page = PageNameConstants::ProductCategoryMenu;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
