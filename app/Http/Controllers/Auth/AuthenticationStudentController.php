<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterStudentRequest;
use App\Services\StudentRegistrationService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthenticationStudentController extends Controller
{
    use ApiSuccessTrait ;

    public function studentRegister(RegisterStudentRequest $request, StudentRegistrationService $studentService): JsonResponse
    {
        $studentService->register($request->validated());

        return $this->successResponse('انشاء الحساب !' , 'تم إرسال رمز التحقق إلى بريدك الإلكتروني.' , 201);
    }
}
