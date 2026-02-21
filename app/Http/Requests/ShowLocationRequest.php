<?php

namespace App\Http\Requests;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;

class ShowLocationRequest extends BaseRequest
{
    protected $page = PageNameConstants::LocationMenu;
    protected $action = ActionConstants::ShowAction;

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
