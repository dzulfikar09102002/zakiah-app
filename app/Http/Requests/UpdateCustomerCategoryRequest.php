<?php

namespace App\Http\Requests;

use App\Enums\CustomerCategoryResetEveryEnum;
use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;
use Illuminate\Validation\Rule;

class UpdateCustomerCategoryRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim($this->name),
        ]);
    }
    
    public function rules(): array
    {
        $customerCategory = $this->route('customerCategory');
        return [
            'name' => [
            'required',
            Rule::unique('customer_categories', 'name')
                ->where(fn ($q) => $q
                    ->where('entity_id', auth()->user()->entity_id)
                    ->whereNull('deleted_at')
                )
                ->ignore($customerCategory?->id),
        ],
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
