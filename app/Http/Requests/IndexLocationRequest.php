<?php

namespace App\Http\Requests;

use App\Helpers\Constants\PageNameConstants;

class IndexLocationRequest extends BaseIndexRequest
{
    protected $page = PageNameConstants::LocationMenu;

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
            'keyword' => 'nullable',
            'code' => 'nullable',
            'status' => 'nullable|array',
        ];
    }
}
