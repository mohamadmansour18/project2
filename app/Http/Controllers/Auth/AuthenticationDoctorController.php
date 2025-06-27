<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorRegisterRequest;
use App\Http\Requests\LoginDocktorRequest;
use App\Http\Requests\OtpResendRequest;
use App\Http\Requests\OtpVerificationRequest;
use App\Services\DoctorService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

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

        return $this->successResponse('اعادة ارسال الرمز !' , 'تم إرسال رمز تحقق جديد إلى بريدك.');
    }

    ///////////////////////////////////////////////////////////////////////////////////

    public function doctorLogin(LoginDocktorRequest $request): JsonResponse
    {
        $response = $this->doctorService->login($request->validated());

        return $this->dataResponse($response , 200);
    }

}
