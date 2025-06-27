<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiSuccessTrait
{
    public function successResponse(string $title , string $body , int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'title' => $title ,
            'body' => $body ,
            'statusCode' => $statusCode
        ] , $statusCode);
    }

    public function dataResponse(array $data , int $statusCode = 200): JsonResponse
    {
        $data['statusCode'] = $statusCode ;
        return response()->json($data , $statusCode);
    }
}
