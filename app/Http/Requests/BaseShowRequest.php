<?php

namespace App\Http\Requests;

use App\Helpers\Constants\ActionConstants;

class BaseShowRequest extends BaseRequest
{
    protected $action = ActionConstants::ShowAction;
}
