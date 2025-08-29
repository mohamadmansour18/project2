<?php

namespace App\Services;

use App\Helpers\UrlHelper;
use App\Repositories\ProfileRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function __construct(
        protected ProfileRepository $profileRepository
    )
    {}

    public function getDoctorProfile(): array
    {
        $doctorId = Auth::id();

        $doctorProfile = $this->profileRepository->findOrFailDoctorProfile($doctorId);
        $isInCommittee = $this->profileRepository->isDoctorInCommitteeCurrentYear($doctorId);

        return [
            'head' => [
                'profile_image' => UrlHelper::imageUrl($doctorProfile?->profile?->profile_image) ?? null,
                'welcome' =>  '! ' . $doctorProfile?->name . ', مرحبا' ?? '',
                'registered_at' => $doctorProfile?->created_at->format('Y-m-d'),
                'role' => 'مشرف' ,
                'is_in_committee' => $isInCommittee,
                'status' => $isInCommittee ?  'ضمن اللجان' : 'لست ضمن لجنة'
            ],
            'main' => [
                'name' => $doctorProfile?->name ?? '',
                'governorate' => $doctorProfile?->profile?->governorate?->value ?? '',
                'email' => $doctorProfile?->email ?? '',
                'phone_number' => $doctorProfile?->profile?->phone_number ?? '',
                'birth_date' => $doctorProfile?->profile?->birth_date ?? '',
            ]
        ];
    }

    public function updateProfile(array $data): void
    {
        $doctorId = Auth::id();

        $profile = $this->profileRepository->findOrFailDoctorProfile($doctorId);

        $this->profileRepository->updateProfile($doctorId , $data);
    }

    public function updateDoctorProfilePicture($file): void
    {
        $doctorId = Auth::id();
        $profile = $this->profileRepository->findOrFailDoctorProfile($doctorId);

        if($profile?->profile?->profile_image && Storage::disk('public')->exists($profile?->profile?->profile_image))
        {
            Storage::disk('public')->delete($profile?->profile?->profile_image);
        }


        $fileName =  $doctorId . '_Doctor_' . time() . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs('doctor_profile_image', $fileName, 'public');

        $this->profileRepository->updateProfile($doctorId , ['profile_image' => $path]);
    }

    public function updateStudentProfilePicture($file): void
    {
        $studentId = Auth::id();
        $profile = $this->profileRepository->findOrFailDoctorProfile($studentId);

        if($profile?->profile?->profile_image && Storage::disk('public')->exists($profile?->profile?->profile_image))
        {
            Storage::disk('public')->delete($profile?->profile?->profile_image);
        }


        $fileName =  $studentId . '_Student_' . time() . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs('student_profile_image', $fileName, 'public');

        $this->profileRepository->updateProfile($studentId , ['profile_image' => $path]);
    }
}
