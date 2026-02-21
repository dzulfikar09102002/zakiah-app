<?php

namespace App\Http\Requests;

use App\Enums\StatusEnum;
use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;
use Illuminate\Validation\Rule;

class UpdateOrderTypeRequest extends BaseRequest
{
    protected $page = PageNameConstants::OrderTypeMenu;
    protected $action = ActionConstants::StoreAction;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => 'nullable',
            "fixed_fee" => 'nullable|integer|min:0',
            "variable_fee" => 'nullable|integer|min:0',
            "require_customer_data" => 'nullable|boolean',
            "payment_method_id" => 'nullable|exists:payment_methods,id',
            "status" => ['nullable', Rule::enum(StatusEnum::class)],
        ];
    }
}
