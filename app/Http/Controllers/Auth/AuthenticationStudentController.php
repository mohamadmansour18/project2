<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginDocktorRequest;
use App\Http\Requests\NewPasswordRequest;
use App\Http\Requests\OtpResendRequest;
use App\Http\Requests\OtpVerificationRequest;
use App\Http\Requests\RegisterStudentRequest;
use App\Services\StudentRegistrationService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthenticationStudentController extends Controller
{
    use ApiSuccessTrait ;

    public StudentRegistrationService $studentService ;

    public function __construct(StudentRegistrationService $studentService)
    {
        $this->studentService = $studentService ;
    }

    public function studentRegister(RegisterStudentRequest $request): JsonResponse
    {
        $this->studentService->register($request->validated());

        return $this->successResponse('انشاء الحساب !' , 'تم إرسال رمز التحقق إلى بريدك الإلكتروني.' , 201);
    }


    public function verifyStudentOtp(OtpVerificationRequest $request ): JsonResponse
    {
        $this->studentService->verifyOtp($request->validated());

        return $this->successResponse('تأكيد الحساب !' , 'تم تأكيد بريدك الالكتروني بنجاح');
    }

    public function resendStudentOtp(OtpResendRequest $request ): JsonResponse
    {
        $this->studentService->resendOtp($request->validated());

        return $this->successResponse('اعادة ارسال الرمز !' , 'تم إرسال رمز تحقق جديد إلى بريدك .');
    }

    ///////////////////////////////////////////////////////////////////////////////////

    public function studentLogin(LoginDocktorRequest $request): JsonResponse
    {
        $response = $this->studentService->login($request->validated());

        return $this->dataResponse($response , 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->studentService->sendPasswordResetOtp($request->email);

        return $this->successResponse('تأكيد البريد المدخل !' , 'تم إرسال رمز التحقق إلى بريدك الإلكتروني المدخل .');
    }

    public function forgotPasswordOtp(OtpVerificationRequest $request): JsonResponse
    {
        $this->studentService->verifyPasswordResetOtp($request->validated());

        return $this->successResponse('تم التأكيد بنجاح !' , 'تم تأكيد بريدك الالكتروني المستخدم لاعادة تعين كلمة المرور .');
    }

    public function resetPassword(NewPasswordRequest $request): JsonResponse
    {
        $this->studentService->resetPassword($request->validated());

        return $this->successResponse('كلمة مرور جديدة !' , 'تم تعيين كلمة المرور الجديدة بنجاح ، يمكنك تسجيل الدخول الآن');
    }

    public function resendPasswordResetOtp(ForgotPasswordRequest $request): JsonResponse
    {
        $this->studentService->resendPasswordResetOtp($request->email);

        return $this->successResponse('اعادة ارسال الرمز !' , 'تم إرسال رمز تحقق جديد إلى بريدك .');
    }

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return $this->successResponse('عملية تسجيل الخروج !' , 'تم تسجيل الخروج من حسابك بنجاح شكرا لاستخدامك تطبيق جامعتي');
    }

}
