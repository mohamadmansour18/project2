<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginException extends Exception
{
    protected string $title ;
    protected string $body ;
    protected ?bool $verify ;
    protected int $statusCode ;

    public function __construct(string $title = 'خطأ في تسجيل الدخول' , string $body = 'حدث خطأ غير متوقع أثناء عملية تسجيل الدخول' , bool $verify = null , int $statusCode = 422)
    {
        $this->title = $title ;
        $this->body = $body ;
        $this->verify = $verify ;
        $this->statusCode = $statusCode ;

        parent::__construct($body, $statusCode);
    }

    public function render(Request $request): JsonResponse
    {
        $response = [
            'title' => $this->title ,
            'body' => $this->body ,
            'statusCode' => $this->statusCode
        ];

        if(!is_null($this->verify))
        {
            $response['verify'] = $this->verify ;
        }
        return response()->json( $response , $this->statusCode);
    }
}
