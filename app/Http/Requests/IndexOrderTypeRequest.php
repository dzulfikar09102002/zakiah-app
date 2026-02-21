<?php

namespace App\Http\Requests;

use App\Helpers\Constants\PageNameConstants;

class IndexOrderTypeRequest extends BaseIndexRequest
{
    protected $page = PageNameConstants::OrderTypeMenu;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'search' => 'nullable',
            'statuses' => 'nullable|array|in:active,archived',
        ];
    }
}
