<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\NewPasswordRequest;
use App\Http\Requests\OtpVerificationRequest;
use App\Services\AdminRegistrationService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthenticationAdminController extends Controller
{
    use ApiSuccessTrait;
    public AdminRegistrationService $adminRegistrationService ;
    public function __construct(AdminRegistrationService $adminRegistrationService)
    {
        $this->adminRegistrationService = $adminRegistrationService ;
    }

    ///////////////////////////////////////////////////////////////////////////////////

    public function AdminLogin(LoginRequest $request): JsonResponse
    {
        $response = $this->adminRegistrationService->login($request->validated());

        return $this->dataResponse($response ,200);
    }

    ///////////////////////////////////////////////////////////////////////////////////

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->adminRegistrationService->sendPasswordResetOtp($request->email);

        return $this->successResponse('تأكيد البريد المدخل !' , 'تم إرسال رمز التحقق إلى بريدك الإلكتروني المدخل .');
    }

    public function forgotPasswordOtp(OtpVerificationRequest $request): JsonResponse
    {
        $this->adminRegistrationService->verifyPasswordResetOtp($request->validated());

        return $this->successResponse('تم التأكيد بنجاح !' , 'تم تأكيد بريدك الالكتروني المستخدم لاعادة تعين كلمة المرور .');
    }

    public function resetPassword(NewPasswordRequest $request): JsonResponse
    {
        $this->adminRegistrationService->resetPassword($request->validated());

        return $this->successResponse('كلمة مرور جديدة !' , 'تم تعيين كلمة المرور الجديدة بنجاح ، يمكنك تسجيل الدخول الآن');
    }

    public function resendPasswordResetOtp(ForgotPasswordRequest $request): JsonResponse
    {
        $this->adminRegistrationService->resendPasswordResetOtp($request->email);

        return $this->successResponse('اعادة ارسال الرمز !' , 'تم إرسال رمز تحقق جديد إلى بريدك .');
    }

    ///////////////////////////////////////////////////////////////////////////////////

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return $this->successResponse('عملية تسجيل الخروج !' , 'تم تسجيل الخروج من حسابك بنجاح لاتتأخر في العودة الينا ايها المدير');
    }
}
