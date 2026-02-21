<?php

namespace App\Http\Requests;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;

class UpdateLoyaltyRequest extends BaseRequest
{
    protected $page = PageNameConstants::LoyaltyMenu;
    protected $action = ActionConstants::UpdateAction;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            "name" => 'required',
            "description" => 'nullable',
            "miniminal_transaction_value" => 'required|integer|min:1',
            "reward_point" => 'required|integer|min:1',
            "conversion_point" => 'nullable|integer|min:1',
            "conversion_amount" => 'nullable|integer|min:1',
            "allow_multiple" => 'boolean',
            "include_discount_and_promo" => 'boolean',
            "include_surcharge" => 'boolean',
            "include_free_of_charge" => 'boolean',
            "include_tax" => 'boolean',
            "include_service_charge" => 'boolean',
            "select_all_location" => 'boolean',
            "allow_convert_point_as_amount" => 'boolean',
            "active" => 'boolean',
            "reward_products" => 'nullable|array',
            'reward_products.*.id' => 'nullable|integer',
            'reward_products.*._destroy' => 'boolean',
            "reward_products.*.product_id" => 'required|integer',
            "reward_products.*.product_unit_id" => 'required|integer',
            "reward_products.*.point_needed" => 'required|integer|min:1',
            "reward_products.*.maximum_quantity" => 'nullable|integer',
        ];
    }
}
