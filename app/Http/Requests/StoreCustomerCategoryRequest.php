<?php

namespace App\Http\Requests;

use App\Enums\CustomerCategoryResetEveryEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerCategoryRequest extends FormRequest
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
        return [
            'name' => [
            'required',
            Rule::unique('customer_categories', 'name')
                ->where(fn ($q) =>
                    $q->where('entity_id', $this->user()->entity->id)
                ),
        ],
            "reset_every" => ['nullable', Rule::enum(CustomerCategoryResetEveryEnum::class)],
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
