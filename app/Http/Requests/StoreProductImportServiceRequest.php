<?php

namespace App\Http\Requests;

use App\Helpers\Constants\ActionConstants;
use App\Helpers\Constants\PageNameConstants;

class StoreProductImportServiceRequest extends BaseRequest
{
    protected $page = PageNameConstants::ProductImportMenu;
    protected $action = ActionConstants::StoreAction;

    public function prepareForValidation(): void {
        if ($this->exists('file')) {
            $this->merge([
                'extension' => strtolower($this->file->getClientOriginalExtension()),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
            'file_url' => 'required',
            'extension' => 'required|in:doc,csv,xlsx,xls,docx,ppt,odt,ods,odp',
            'auto_approve' => 'nullable',
            'request_note' => 'nullable',
            'note' => 'nullable',
        ];
    }
}
