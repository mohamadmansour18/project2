<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorRegisterRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginDocktorRequest;
use App\Http\Requests\NewPasswordRequest;
use App\Http\Requests\OtpResendRequest;
use App\Http\Requests\OtpVerificationRequest;
use App\Services\DoctorService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthenticationDoctorController extends Controller
{
    use ApiSuccessTrait ;

    public DoctorService $doctorService ;

    public function __construct(DoctorService $doctorService)
    {
        $this->doctorService = $doctorService ;
    }

    ///////////////////////////////////////////////////////////////////////////////////
    public function doctorRegister(DoctorRegisterRequest $request): JsonResponse
    {
        $this->doctorService->registerDoctor($request->validated());

        return $this->successResponse('انشاء الحساب !' , 'تم إرسال رمز التحقق إلى بريدك الإلكتروني.' , 201);
    }

    public function verifyDoctorOtp(OtpVerificationRequest $request ): JsonResponse
    {
        $this->doctorService->verifyOtp($request->validated());

        return $this->successResponse('تأكيد الحساب !' , 'تم تأكيد بريدك الالكتروني بنجاح');
    }

    public function resendDoctorOtp(OtpResendRequest $request ): JsonResponse
    {
        $this->doctorService->resendOtp($request->validated());

        return $this->successResponse('اعادة ارسال الرمز !' , 'تم إرسال رمز تحقق جديد إلى بريدك .');
    }

    ///////////////////////////////////////////////////////////////////////////////////

    public function doctorLogin(LoginDocktorRequest $request): JsonResponse
    {
        $response = $this->doctorService->login($request->validated());

        return $this->dataResponse($response , 200);
    }

    ///////////////////////////////////////////////////////////////////////////////////

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $this->doctorService->sendPasswordResetOtp($request->email);

        return $this->successResponse('تأكيد البريد المدخل !' , 'تم إرسال رمز التحقق إلى بريدك الإلكتروني المدخل .');
    }

    public function forgotPasswordOtp(OtpVerificationRequest $request): JsonResponse
    {
        $this->doctorService->verifyPasswordResetOtp($request->validated());

        return $this->successResponse('تم التأكيد بنجاح !' , 'تم تأكيد بريدك الالكتروني المستخدم لاعادة تعين كلمة المرور .');
    }

    public function resetPassword(NewPasswordRequest $request): JsonResponse
    {
        $this->doctorService->resetPassword($request->validated());

        return $this->successResponse('كلمة مرور جديدة !' , 'تم تعيين كلمة المرور الجديدة بنجاح ، يمكنك تسجيل الدخول الآن');
    }

    public function resendPasswordResetOtp(ForgotPasswordRequest $request): JsonResponse
    {
        $this->doctorService->resendPasswordResetOtp($request->validated());

        return $this->successResponse('اعادة ارسال الرمز !' , 'تم إرسال رمز تحقق جديد إلى بريدك .');
    }

    ///////////////////////////////////////////////////////////////////////////////////

    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return $this->successResponse('عملية تسجيل الخروج !' , 'تم تسجيل الخروج من حسابك بنجاح شكرا لاستخدامك تطبيق جامعتي');
    }

}
