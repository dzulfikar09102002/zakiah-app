<?php

namespace App\Http\Requests;

use App\Enums\TaxSettingEnum;
use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends BaseRequest
{
    protected $page = PageNameConstants::ProductMenu;
    protected $action = ActionConstants::UpdateAction;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => 'nullable',
            "sku" => 'nullable',
            "barcode" => 'nullable',
            "description" => 'nullable',
            "sell_price" => 'nullable|integer|min:0',
            "last_buying_price" => 'required|integer|min:0',
            "product_category_id" => 'nullable',
            "child_product_category_id" => 'nullable',
            "product_unit_id" => 'nullable',
            "product_sell_unit_id" => 'nullable',
            "location_id" => 'nullable', # need to check base on entity
            "image_url" => 'nullable|image',
            "sell_to_customer" => 'nullable|boolean',
            "service" => 'nullable|boolean',
            "modifier" => 'nullable|boolean',
            "allow_custom_price" => 'nullable|boolean',
            "select_all_location" => 'nullable|boolean',
            "location_ids" => 'nullable|array',
            "exclude_location_ids" => 'nullable|array',
            "tax_id" => 'nullable',
            "tax_setting" => ['nullable', Rule::enum(TaxSettingEnum::class)],
            "stock_movements" => 'nullable|array',
            "stock_movements.*.location_id" => 'required',
            "stock_movements.*.stock" => 'required',
            "stock_movements.*.buying_price" => 'required',
            "product_sell_prices" => 'nullable',
            "supplier_name" => 'nullable',
        ];
    }
}
