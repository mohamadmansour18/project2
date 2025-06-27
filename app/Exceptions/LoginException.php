<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginException extends Exception
{
    protected string $title ;
    protected string $body ;
    protected bool $verify ;
    protected int $statusCode ;

    public function __construct(string $title = 'خطأ في تسجيل الدخول' , string $body = 'حدث خطأ غير متوقع أثناء عملية تسجيل الدخول' , bool $verify = true , int $statusCode = 422)
    {
        $this->title = $title ;
        $this->body = $body ;
        $this->verify = $verify ;
        $this->statusCode = $statusCode ;

        parent::__construct($body, $statusCode);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'title' => $this->title ,
            'body' => $this->body ,
            'verify' => $this->verify ,
            'statusCode' => $this->statusCode
        ] , $this->statusCode);
    }
}
