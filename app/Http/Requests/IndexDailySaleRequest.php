<?php

namespace App\Http\Requests;

use App\Helpers\Constants\PageNameConstants;

class IndexDailySaleRequest extends BaseIndexRequest
{
    protected $page = PageNameConstants::DailySalesMenu;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'loc' => 'required',
            'start_at' => 'required|date',
            'end_at' => 'required|date',
        ];
    }
}
