<?php

namespace App\Http\Requests;

use App\Enums\CustomerCategoryResetEveryEnum;
use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;
use Illuminate\Validation\Rule;

class UpdateCustomerCategoryRequest extends BaseRequest
{
    protected $page = PageNameConstants::CustomerCategoryMenu;
    protected $action = ActionConstants::UpdateAction;
    
    public function rules(): array
    {
        return [
            "name" => 'nullable',
            "required" => 'nullable|boolean',
            "reset_every" => ['nullable', Rule::enum(CustomerCategoryResetEveryEnum::class)],
            // "status" => ['nullable', Rule::enum(StatusEnum::class)],
            "customer_category_rule" => 'nullable',
            "customer_category_rule.minimal_spend" => 'nullable|integer|min:0',
            "customer_category_rule.include_tax" => 'boolean',
            "customer_category_rule.include_service_charge" => 'boolean',
            "customer_category_rule.include_promo" => 'boolean',
            "customer_category_rule.include_surcharge" => 'boolean',
            "customer_category_rule.include_free_of_charge" => 'boolean',
        ];
    }
}
