<?php

namespace App\Http\Requests;

use App\Enums\AvailableChannelEnum;
use App\Enums\PromoConditionEnum;
use App\Enums\PromoGoalEnum;
use App\Enums\PromoRewardAppliedToEnum;
use App\Enums\PromoRewardTemplateEnum;
use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;
use Illuminate\Validation\Rule;

class StorePromoRequest extends BaseRequest
{
    protected $page = PageNameConstants::PromoMenu;
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
            'owner_location_id' => 'required|exists:locations,id',
            'name' => 'required',
            'description' => 'nullable',
            'channel' => ['nullable', Rule::enum(AvailableChannelEnum::class)],
            'start_at' => 'required|date',
            'end_at' => 'nullable|date',
            'goal' => ['nullable', Rule::enum(PromoGoalEnum::class)],
            'auto_apply' => 'boolean',
            'combine_promo' => 'boolean',
            'select_all_location' => 'boolean',
            'location_ids' => 'nullable|exists:location,id',
            'exclude_location_ids' => 'nullable|exists:location,id',
            'promo_rule' => 'required',
            'promo_rule.minimum_sales_purchase' => 'nullable|integer|min:0',
            'promo_rule.customer_only' => 'boolean',
            'promo_rule.customer_category_ids' => 'nullable|array|exists:customer_categories,id',
            'promo_rule.order_type_ids' => 'nullable|array|exists:order_types,id',
            'promo_rule.product_buy_condition' => ['nullable', Rule::enum(PromoConditionEnum::class)],
            'promo_rule.product_category_buy_condition' => ['nullable', Rule::enum(PromoConditionEnum::class)],
            'promo_rule.products' => 'nullable|array',
            'promo_rule.products.*.product_id' => 'nullable|integer',
            'promo_rule.products.*.product_category_id' => 'nullable|integer',
            'promo_rule.products.*.product_unit_id' => 'nullable|integer',
            'promo_rule.products.*.minimum_purchase' => 'nullable|integer|min:0',
            'promo_reward' => 'required',
            'promo_reward.template' => ['required', Rule::enum(PromoRewardTemplateEnum::class)],
            'promo_reward.applied_to' => ['required', Rule::enum(PromoRewardAppliedToEnum::class)],
            'promo_reward.percentage' => 'boolean',
            'promo_reward.reward_amount' => 'nullable|integer|min:0',
            'promo_reward.reward_maximum_amount' => 'nullable|integer|min:0',
            'promo_reward.in_house_percentage' => 'required|integer|min:0|max:100',
            'promo_reward.products' => 'nullable|array',
            'promo_reward.products.*.product_id' => 'nullable|integer',
            'promo_reward.products.*.product_category_id' => 'nullable|integer',
            'promo_reward.products.*.sell_price' => 'nullable|integer',
            'promo_reward.products.*.reward_quantity' => 'nullable|integer',
            'promo_reward.products.*.maximum_reward_quantity' => 'nullable|integer',
            'promo_reward.products.*.reward_maximum_amount' => 'nullable|integer',
        ];
    }
}
