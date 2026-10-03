<?php

namespace App\Http\Requests;

use App\Enums\PromoRewardTemplateEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePromoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Field mengikuti form promo backoffice lama (pos-secaca); nilai lain diisi default di PromoService.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $entityId = $this->user()?->entity?->id;

        return [
            'owner_location_id' => [
                'required',
                Rule::exists('locations', 'id')->where('entity_id', $entityId),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'promo_rule' => 'required|array',
            'promo_rule.minimum_sales_purchase' => 'nullable|integer|min:0',
            'promo_rule.customer_category_ids' => 'nullable|array',
            'promo_rule.customer_category_ids.*' => [
                'integer',
                Rule::exists('customer_categories', 'id')->where('entity_id', $entityId),
            ],
            'promo_reward' => 'required|array',
            'promo_reward.template' => [
                'required',
                Rule::in([
                    PromoRewardTemplateEnum::DiscountPercentage->value,
                    PromoRewardTemplateEnum::DiscountFixed->value,
                ]),
            ],
            'promo_reward.reward_amount' => [
                'required',
                'integer',
                'min:1',
                Rule::when(
                    $this->input('promo_reward.template') === PromoRewardTemplateEnum::DiscountPercentage->value,
                    'max:100'
                ),
            ],
            'promo_reward.reward_maximum_amount' => 'nullable|integer|min:0',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'owner_location_id' => 'lokasi',
            'name' => 'nama promo',
            'start_at' => 'tanggal mulai',
            'end_at' => 'tanggal selesai',
            'promo_rule.minimum_sales_purchase' => 'minimal belanja',
            'promo_rule.customer_category_ids' => 'kategori pelanggan',
            'promo_reward.template' => 'jenis diskon',
            'promo_reward.reward_amount' => 'besaran diskon',
            'promo_reward.reward_maximum_amount' => 'maksimal diskon',
        ];
    }
}
