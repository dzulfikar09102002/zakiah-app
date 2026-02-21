<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Response;

class BaseJsonResponse extends BaseResponse
{
    public function response(int $code = 200) {
        return Response::json($this->build(), $code);
    }
}
