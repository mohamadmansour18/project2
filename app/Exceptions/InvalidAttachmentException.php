<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvalidAttachmentException extends Exception
{
    protected string $title;
    protected string $body;
    protected int $statusCode;

    public function __construct(
        string $title = 'مرفق غير صالح',
        string $body = 'الملف المرفوع لا يتوافق مع النوع المطلوب',
        int $statusCode = 422
    ) {
        $this->title = $title;
        $this->body = $body;
        $this->statusCode = $statusCode;

        parent::__construct($body, $statusCode);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'title' => $this->title,
            'body' => $this->body,
            'statusCode' => $this->statusCode
        ], $this->statusCode);
    }
}
