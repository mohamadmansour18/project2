<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorRegisterRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\NewPasswordRequest;
use App\Http\Requests\OtpResendRequest;
use App\Http\Requests\OtpVerificationRequest;
use App\Services\DoctorRegistrationService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthenticationDoctorController extends Controller
{
    use ApiSuccessTrait ;

    public DoctorRegistrationService $doctorRegistrationService ;

    public function __construct(DoctorRegistrationService $doctorRegistrationService)
    {
        $this->doctorRegistrationService = $doctorRegistrationService ;
    }

    ///////////////////////////////////////////////////////////////////////////////////
    public function doctorRegister(DoctorRegisterRequest $request): JsonResponse
    {
        $this->doctorRegistrationService->registerDoctor($request->validated());

        return $this->successResponse('! انشاء الحساب' , 'تم إرسال رمز التحقق إلى بريدك الإلكتروني.' , 201);
    }

    public function verifyDoctorOtp(OtpVerificationRequest $request ): JsonResponse
    {
        $this->doctorRegistrationService->verifyOtp($request->validated());

        return $this->successResponse('! تأكيد الحساب' , 'تم تأكيد بريدك الالكتروني بنجاح');
    }

    public function resendDoctorOtp(OtpResendRequest $request ): JsonResponse
    {
        $this->doctorRegistrationService->resendOtp($request->validated());

        return $this->successResponse('! اعادة ارسال الرمز' , 'تم إرسال رمز تحقق جديد إلى بريدك .');
    }

    ///////////////////////////////////////////////////////////////////////////////////

    public function doctorLogin(LoginRequest $request): JsonResponse
    {
        $response = $this->doctorRegistrationService->login($request->validated());

        return $this->dataResponse($response , 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->doctorRegistrationService->sendPasswordResetOtp($request->email);

        return $this->successResponse('! تأكيد البريد المدخل' , 'تم إرسال رمز التحقق إلى بريدك الإلكتروني المدخل .');
    }

    public function forgotPasswordOtp(OtpVerificationRequest $request): JsonResponse
    {
        $this->doctorRegistrationService->verifyPasswordResetOtp($request->validated());

        return $this->successResponse('! تم التأكيد بنجاح' , 'تم تأكيد بريدك الالكتروني المستخدم لاعادة تعين كلمة المرور .');
    }

    public function resetPassword(NewPasswordRequest $request): JsonResponse
    {
        $this->doctorRegistrationService->resetPassword($request->validated());

        return $this->successResponse('! كلمة مرور جديدة' , 'تم تعيين كلمة المرور الجديدة بنجاح ، يمكنك تسجيل الدخول الآن');
    }

    public function resendPasswordResetOtp(ForgotPasswordRequest $request): JsonResponse
    {
        $this->doctorRegistrationService->resendPasswordResetOtp($request->email);

        return $this->successResponse('! اعادة ارسال الرمز' , 'تم إرسال رمز تحقق جديد إلى بريدك .');
    }

    ///////////////////////////////////////////////////////////////////////////////////

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return $this->successResponse('! عملية تسجيل الخروج' , 'تم تسجيل الخروج من حسابك بنجاح شكرا لاستخدامك تطبيق جامعتي');
    }

}
