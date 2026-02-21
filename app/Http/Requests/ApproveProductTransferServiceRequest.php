<?php

namespace App\Http\Requests;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;

class ApproveProductTransferServiceRequest extends BaseRequest
{
    protected $page = PageNameConstants::ProductTransferMenu;
    protected $action = ActionConstants::ApproveAction;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'notes' => 'required',
        ];
    }
}
