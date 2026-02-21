<?php

namespace App\Http\Requests;

use App\Helpers\Constants\PageNameConstants;

class PreviewProductOpnameServiceRequest extends BaseShowRequest
{
    protected $page = PageNameConstants::ProductOpnameMenu;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'limit' => 'nullable|integer',
            'page' => 'nullable|integer',
            'show_all' => 'required|in:true,false',
            'show_difference' => 'required|in:true,false',
        ];
    }
}
