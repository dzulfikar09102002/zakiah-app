<?php

namespace App\Http\Requests;

use App\Helpers\Constants\PageNameConstants;

class IndexProductRequest extends BaseIndexRequest
{
    protected $page = PageNameConstants::ProductMenu;
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'limit' => 'required|integer|min:0',
            'keyword' => 'nullable',
            'selected_ids' => 'nullable|array',
            'search' => 'nullable',
            'statuses' => 'nullable|array',
            'barcode_mode' => 'nullable|in:true,false',
            'parent_only' => 'nullable|in:true,false',
            'flatten_variance' => 'nullable|in:true,false',
            'selectAllProductCategory' => 'nullable|in:true,false',
            'excludeProductCategoryIds' => 'nullable|array',
            'productCategoryIds' => 'nullable|array',
        ];
    }
}
