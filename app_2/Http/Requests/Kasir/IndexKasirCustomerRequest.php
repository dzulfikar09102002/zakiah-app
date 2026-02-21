<?php

namespace App\Http\Requests\Kasir;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;

class IndexKasirCustomerRequest extends BaseRequest
{
    protected $page = PageNameConstants::CustomerMenu;
    protected $action = ActionConstants::IndexAction;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'keyword' => 'required',
        ];
    }
}
