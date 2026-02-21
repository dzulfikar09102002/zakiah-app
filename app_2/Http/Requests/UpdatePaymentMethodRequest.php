<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethodKindEnum;
use App\Enums\StatusEnum;
use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;
use Illuminate\Validation\Rule;

class UpdatePaymentMethodRequest extends BaseRequest
{
    protected $page = PageNameConstants::PaymentMethodMenu;
    protected $action = ActionConstants::UpdateAction;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "name" => 'nullable',
            "kind" => ['nullable', Rule::enum(PaymentMethodKindEnum::class)],
            "status" => ['nullable', Rule::enum(StatusEnum::class)],
            "icon_image_url" => 'nullable|image',
            "fixed_fee" => 'nullable|integer|min:0',
            "variable_fee" => 'nullable|integer|min:0',
        ];
    }
}
