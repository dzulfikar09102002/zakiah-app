<?php

namespace App\Http\Requests;

use App\Enums\TaxSettingEnum;
use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;
use Illuminate\Validation\Rule;

class StoreProductRequest extends BaseRequest
{
    protected $page = PageNameConstants::ProductMenu;
    protected $action = ActionConstants::StoreAction;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $forImport = (bool) request()->boolean('for_import');
        $entityId = auth()->user()?->entity?->id;
        return [
            "name" => 'required',
            "sku" => $forImport
                ? "required"
                : [
                    "required",
                    Rule::unique('products', 'sku')
                        ->where(fn ($query) => $query->where('entity_id', $entityId)),
                ],

            "barcode" => $forImport
                ? "required"
                : [
                    "required",
                    Rule::unique('products', 'barcode')
                        ->where(fn ($query) => $query->where('entity_id', $entityId)),
                ],
            "description" => 'nullable',
            "sell_price" => 'required|integer|min:0',
            "last_buying_price" => 'required|integer|min:0',
            "product_category_id" => 'nullable',
            "child_product_category_id" => 'nullable',
            "product_unit_id" => 'required',
            "product_sell_unit_id" => 'required',
            "location_id" => 'required', 
            "image_url" => 'nullable|image',
            "sell_to_customer" => 'boolean',
            "service" => 'boolean', 
            "modifier" => 'boolean',
            "allow_custom_price" => 'boolean',
            "select_all_location" => 'boolean',
            "location_ids" => 'nullable|array',
            "exclude_location_ids" => 'nullable|array',
            "tax_id" => 'nullable',
            "tax_setting" => ['nullable', Rule::enum(TaxSettingEnum::class)],
            "product_unit_conversions" => 'nullable',
            "product_unit_conversions.*.unit_id" => 'required',
            "product_unit_conversions.*.quantity" => 'required|integer|min:0',
            "product_unit_conversions.*.internal_price" => 'required|integer|min:0',
            "stock_movements" => 'nullable|array',
            "stock_movements.*.location_id" => 'required',
            "stock_movements.*.stock" => 'required',
            "stock_movements.*.buying_price" => 'required',
            "product_sell_prices" => 'nullable',
            "product_sell_prices.*.location_id" => 'nullable',
            "product_sell_prices.*.order_type_id" => 'nullable',
            "product_sell_prices.*.product_unit_id" => 'nullable',
            "product_sell_prices.*.tax_id" => 'nullable',
            "product_sell_prices.*.tax_setting" => ['nullable', Rule::enum(TaxSettingEnum::class)],
            "product_sell_prices.*.sell_price" => 'required|integer|min:0',
        ];
    }
}
