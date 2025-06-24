<?php

namespace App\Http\Controllers;

use App\Http\Requests\OtpResendRequest;
use App\Http\Requests\OtpVerificationRequest;
use App\Http\Requests\DoctorRegisterRequest;
use App\Services\DoctorService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthenticationController extends Controller
{
    use ApiSuccessTrait ;

    public DoctorService $supervisorService ;

    public function __construct(DoctorService $supervisorService)
    {
        $this->supervisorService = $supervisorService ;
    }

    ///////////////////////////////////////////////////////////////////////////////////
    public function doctorRegister(DoctorRegisterRequest $request ): JsonResponse
    {
        $this->supervisorService->registerDoctor($request->validated());

        return $this->successResponse('انشاء الحساب !' , 'تم إرسال رمز التحقق إلى بريدك الإلكتروني.' , 201);
    }

    public function verifyDoctorOtp(OtpVerificationRequest $request ): JsonResponse
    {
        $this->supervisorService->verifyOtp($request->validated());

        return $this->successResponse('تأكيد الحساب !' , 'تم تأكيد بريدك الالكتروني بنجاح');
    }

    public function resendDoctorOtp(OtpResendRequest $request ): JsonResponse
    {
        $this->supervisorService->resendOtp($request->validated());

        return $this->successResponse('اعادة ارسال الرمز !' , 'تم إرسال رمز تحقق جديد إلى بريدك.');
    }
}
