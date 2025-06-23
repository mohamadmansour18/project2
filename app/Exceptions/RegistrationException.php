<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegistrationException extends Exception
{
    protected string $title ;
    protected string $body ;
    protected int $statusCode ;

    public function __construct(string $title = "خطأ في التسجيل" , string $body = "حدث خطأ غير متوقع أثناء عملية التسجيل" , int $statusCode = 422)
    {
        $this->title = $title ;
        $this->body = $body ;
        $this->statusCode = $statusCode ;

        parent::__construct($body, $statusCode);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'title' => $this->title ,
            'body' => $this->body ,
            'statusCode' => $this->statusCode
        ] , $this->statusCode);
    }

}
