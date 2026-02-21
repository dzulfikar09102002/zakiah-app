<?php

namespace App\Http\Requests;

use App\Helpers\Constants\PageNameConstants;

class IndexSaleTransactionRequest extends BaseIndexRequest
{
    protected $page = PageNameConstants::SaleTransactionMenu;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'start_at' => 'required|date',
            'end_at' => 'required|date',
            'select_all_location' => 'required|in:true,false',
            'locs' => 'nullable|array',
            'exclude_locs' => 'nullable|array',
            'order_types' => 'nullable|array',
        ];
    }
}
