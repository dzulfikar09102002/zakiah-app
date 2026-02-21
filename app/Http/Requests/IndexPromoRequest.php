<?php

namespace App\Http\Requests;

use App\Helpers\Constants\PageNameConstants;

class IndexPromoRequest extends BaseIndexRequest
{
    protected $page = PageNameConstants::PromoMenu;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'limit' => 'required|integer|min:0',
            'page' => 'nullable|integer|min:0',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date',
            'keyword' => 'nullable',
            'select_all_location' => 'required|in:true,false',
            'locs' => 'nullable|array',
            'exclude_locs' => 'nullable|array',
            'channels' => ['nullable', 'array'],
            'statuses' => ['nullable', 'array'],
        ];
    }
}
