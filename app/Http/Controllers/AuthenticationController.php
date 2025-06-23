<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupervisorRegisterRequest;
use App\Services\SupervisorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthenticationController extends Controller
{
    public function adminRegister(SupervisorRegisterRequest $request , SupervisorService $supervisorService): JsonResponse
    {
        $supervisorService->registerSupervisor($request->validated());

        return response()->json([
            'message' => 'تم إرسال رمز التحقق إلى بريدك الإلكتروني.'
        ] ,200);
    }
}
