<?php

namespace App\Http\Requests;

use App\Enums\CustomerPointTypeEnum;
use App\Helpers\Constants\PageNameConstants;
use App\Helpers\Services\Role\RoleValidatorService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCustomerPointMovementRequest extends BaseIndexRequest
{
    protected $page = PageNameConstants::CustomerPointMenu;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'customer_id' => 'required|integer',
            'limit' => 'nullable|integer',
            'start_at' => 'required|date',
            'end_at' => 'required|date',
            'locs' => 'required|array',
            'types' => [
                'nullable',
                'array',
                Rule::in(CustomerPointTypeEnum::toArray()),
            ],
        ];
    }
}
