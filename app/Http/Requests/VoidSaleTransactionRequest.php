<?php

namespace App\Http\Requests;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;

class VoidSaleTransactionRequest extends BaseRequest
{
    protected $page = PageNameConstants::SaleTransactionMenu;
    protected $action = ActionConstants::VoidAction;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            "reason" => 'required',
            "notes" => 'required',
        ];
    }
}
