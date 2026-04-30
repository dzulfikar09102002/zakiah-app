<?php

namespace App\Http\Responses;

use Illuminate\Http\Response;

class BaseResponse
{
    protected string $message;
    protected array $errors;
    protected $data;

    public function __construct($data, string $message = '', array $errors = [])
    {
        $this->message = $message;
        $this->errors = $errors;
        $this->data = $data;
    }

    protected function build()
    {
        return [
            'message' => $this->message,
            'errors' => $this->errors,
            'data' => $this->data,
        ];
    }

    public function response(int $code = 200)
    {
        return $this->build();
    }
}
