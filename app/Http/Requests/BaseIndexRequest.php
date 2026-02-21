<?php

namespace App\Http\Requests;

use App\Helpers\Constants\ActionConstants;

class BaseIndexRequest extends BaseRequest
{
    protected $action = ActionConstants::IndexAction;
}
