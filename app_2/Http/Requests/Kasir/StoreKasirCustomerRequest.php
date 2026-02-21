<?php

namespace App\Http\Requests\Kasir;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;
use App\Http\Requests\BaseRequest;

class StoreKasirCustomerRequest extends BaseRequest
{
    protected $page = PageNameConstants::CustomerMenu;
    protected $action = ActionConstants::StoreAction;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            "location_id" => 'required',
            "first_name" => 'required',
            "last_name" => 'required',
            "phone_number" => 'required',
            "phone_number_country_code" => 'required',
            "email" => 'nullable|email',
        ];
    }
}
