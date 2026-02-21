<?php

namespace App\Http\Requests;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductAdjustmentStockRequest extends FormRequest
{
    protected $page = PageNameConstants::ProductAdjustmentStockMenu;
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
            'location_id' => 'required|exists:locations,id',
            'note' => 'nullable',
            'auto_approve' => 'boolean',
            'recorded_product_count' => 'required|integer',
            'counted_product_count' => 'required|integer',
            'difference_product_count' => 'required|integer',
            'recorded_stock' => 'required|integer',
            'counted_stock' => 'required|integer',
            'difference_stock' => 'required|integer',
            'products' => 'required',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.product_category_id' => 'nullable|exists:product_categories,id',
            'products.*.product_unit_id' => 'required|exists:product_units,id',
            'products.*.recorded_stock' => 'required|integer',
            'products.*.counted_stock' => 'required|integer',
            'products.*.difference_stock' => 'required|integer',
            'products.*.note' => 'nullable',
        ];
    }
}
