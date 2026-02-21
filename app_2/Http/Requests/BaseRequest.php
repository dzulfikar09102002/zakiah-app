<?php

namespace App\Http\Requests;

use App\Helpers\Services\Role\RoleValidatorService;
use Illuminate\Foundation\Http\FormRequest;

class BaseRequest extends FormRequest
{
    protected $page;
    protected $action;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return (new RoleValidatorService($this->employee, $this->page, $this->action))->authorize();
    }
}
