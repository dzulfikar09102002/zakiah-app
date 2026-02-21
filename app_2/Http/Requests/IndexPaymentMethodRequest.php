<?php

namespace App\Http\Requests;

use App\Helpers\Constants\PageNameConstants;

class IndexPaymentMethodRequest extends BaseIndexRequest
{
    protected $page = PageNameConstants::PaymentMethodMenu;

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
            'search' => 'nullable',
            'status' => 'nullable',
            'statuses' => ['nullable', 'array'],
            'kinds' => ['nullable', 'array'],
        ];
    }
}
