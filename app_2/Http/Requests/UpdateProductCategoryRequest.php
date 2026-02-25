<?php

namespace App_2\Http\Requests;

use App\Enums\StatusEnum;
use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;
use Illuminate\Validation\Rule;

class UpdateProductCategoryRequest extends BaseRequest
{
    protected $page = PageNameConstants::ProductCategoryMenu;
    protected $action = ActionConstants::UpdateAction;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "parent_id" => 'nullable',
            "name" => 'nullable',
            "status" => ['nullable', Rule::enum(StatusEnum::class)],
        ];
    }
}
