<?php
namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionDeniedException extends Exception
{
    protected string $title;
    protected string $body;
    protected int $statusCode;

    public function __construct(
        string $title = 'صلاحيات غير كافية',
        string $body = 'ليس لديك صلاحية لتنفيذ هذا الإجراء',
        int $statusCode = 403
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
