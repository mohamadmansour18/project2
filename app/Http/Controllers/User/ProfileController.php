<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\updateDoctorProfile;
use App\Http\Requests\UpdatePictureRequest;
use App\Services\ProfileService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    use ApiSuccessTrait;

    public function __construct(
        protected ProfileService $profileService
    )
    {}
    public function getDoctorProfile(): JsonResponse
    {
        $data = $this->profileService->getDoctorProfile();

        return $this->dataResponse($data , 200);
    }

    public function updateDoctorProfile(updateDoctorProfile $request): JsonResponse
    {
        $this->profileService->updateProfile($request->validated());

        return $this->successResponse('تمت العملية بنجاح !' , 'تم تحديث معلومات ملفك الشخصي بنجاح' ,200);
    }

    public function updateProfileDoctorPicture(UpdatePictureRequest $request): JsonResponse
    {
        $this->profileService->updateDoctorProfilePicture($request->file('profile_image'));

        return $this->successResponse('تمت العملية بنجاح !' , 'تم تحديث صورة الملف الشخصي بنجاح' , 200);
    }

    public function updateProfileStudentPicture(UpdatePictureRequest $request): JsonResponse
    {
        $this->profileService->updateStudentProfilePicture($request->file('profile_image'));

        return $this->successResponse('تمت العملية بنجاح !' , 'تم تحديث صورة الملف الشخصي بنجاح' , 200);
    }
}
