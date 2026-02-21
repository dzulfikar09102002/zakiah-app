<?php

namespace App\Http\Requests;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;

class UpdateProductTransferServiceRequest extends BaseRequest
{
    protected $page = PageNameConstants::ProductTransferMenu;
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
            'from_location_id' => 'required|integer|exists:locations,id',
            'to_location_id' => 'required|integer|exists:locations,id',
            'request_note' => 'nullable',
            'auto_approve' => 'boolean',
            'products' => 'required|array',
            'products.*.id' => 'nullable|integer',
            'products.*._destroy' => 'boolean',
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
        ];
    }
}
