<?php

namespace App\Helpers\Exceptions;
use Illuminate\Validation\ValidationException;

class BadRequestException extends ValidationException
{

    /**
     * The status code to use for the response.
     *
     * @var int
     */
    public $status = 400;
}
