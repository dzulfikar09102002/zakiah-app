<?php

namespace App\Http\Requests;

use App\Enums\StatusEnum;
use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;
use Illuminate\Validation\Rule;

class UpdateTaxRequest extends BaseRequest
{
    protected $page = PageNameConstants::TaxMenu;
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
            "rate" => 'nullable|integer|min:0',
            "status" => ['nullable', Rule::enum(StatusEnum::class)],
        ];
    }
}
