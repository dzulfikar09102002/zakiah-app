<?php

namespace App\Http\Requests;

use App\Helpers\Constants\PageNameConstants;
use Illuminate\Foundation\Http\FormRequest;

class ShowTaxRequest extends BaseShowRequest
{
    protected $page = PageNameConstants::TaxMenu;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
