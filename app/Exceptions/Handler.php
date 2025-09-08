<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    protected function shouldReturnJson($request , Throwable $e): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }
    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (Throwable $e , $request) {
            if(!$this->shouldReturnJson($request , $e))
            {
                return null;
            }


            if (method_exists($e, 'render')) {
                return null;
            }

            if($e instanceof HttpExceptionInterface && $e->getStatusCode() !== 500)
            {
                return null;
            }

            $status = 500;
            $body = config('app.debug')
                ? ($e->getMessage() ?: '! Server Error')
                : 'Something unexpected happened Server Error';

            return response()->json([
                'title'       => 'server error',
                'body'        => $body ,
                'status_code' => $status,
            ] , $status , [] , JSON_UNESCAPED_UNICODE);
        });
    }

    public function render($request, Throwable $e)
    {
        if ($e instanceof TooManyRequestsHttpException) {
            return response()->json([
                'title' => '! محاولات كثيرة' ,
                'body' => 'لقد قمت بفعل محاولات كثيرة يرجى التجربة لاحقا',
                'statusCode' => 429
            ], 429);
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'title' => '! عنصر غير موجود' ,
                'body' => 'العنصر الذي تحاول الوصول اليه غير موجود في النظام',
                'statusCode' => 404
            ], 404);
        }

        return parent::render($request, $e);
    }
}
