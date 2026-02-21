<?php

namespace App\Http\Requests;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;

class UpdateProductOpnameServiceRequest extends BaseRequest
{
    protected $page = PageNameConstants::ProductOpnameMenu;
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
            'location_id' => 'nullable|exists:locations,id',
            'note' => 'nullable',
            'recorded_product_count' => 'nullable|integer',
            'counted_product_count' => 'nullable|integer',
            'difference_product_count' => 'nullable|integer',
            'recorded_stock' => 'nullable|integer',
            'counted_stock' => 'nullable|integer',
            'difference_stock' => 'nullable|integer',
            'products' => 'required',
            'products.*.id' => 'nullable',
            'products.*._deleted' => 'nullable|boolean',
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
