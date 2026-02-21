<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethodKindEnum;
use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;
use Illuminate\Validation\Rule;

class StorePaymentMethodRequest extends BaseRequest
{
    protected $page = PageNameConstants::PaymentMethodMenu;
    protected $action = ActionConstants::StoreAction;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => 'required',
            "kind" => ['required', Rule::enum(PaymentMethodKindEnum::class)],
            "icon_image_url" => 'nullable|image',
            "fixed_fee" => 'required|integer|min:0',
            "variable_fee" => 'required|integer|min:0',
        ];
    }
}
