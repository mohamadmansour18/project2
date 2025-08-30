<?php

namespace App\Services;

use App\Exceptions\PermissionDeniedException;
use App\Helpers\UrlHelper;
use App\Repositories\ProfileRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    public function __construct(
        protected ProfileRepository $profileRepository
    )
    {
    }

    public function getDoctorProfile(): array
    {
        $doctorId = Auth::id();

        $doctorProfile = $this->profileRepository->findOrFailDoctorProfile($doctorId);
        $isInCommittee = $this->profileRepository->isDoctorInCommitteeCurrentYear($doctorId);

        return [
            'head' => [
                'profile_image' => UrlHelper::imageUrl($doctorProfile?->profile?->profile_image) ?? null,
                'welcome' => '! ' . $doctorProfile?->name . ', مرحبا' ?? '',
                'registered_at' => $doctorProfile?->created_at->format('Y-m-d'),
                'role' => 'مشرف',
                'is_in_committee' => $isInCommittee,
                'status' => $isInCommittee ? 'ضمن اللجان' : 'لست ضمن لجنة'
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

        $this->profileRepository->updateProfile($doctorId, $data);
    }

    public function updateDoctorProfilePicture($file): void
    {
        $doctorId = Auth::id();
        $profile = $this->profileRepository->findOrFailDoctorProfile($doctorId);

        if ($profile?->profile?->profile_image && Storage::disk('public')->exists($profile?->profile?->profile_image)) {
            Storage::disk('public')->delete($profile?->profile?->profile_image);
        }


        $fileName = $doctorId . '_Doctor_' . time() . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs('doctor_profile_image', $fileName, 'public');

        $this->profileRepository->updateProfile($doctorId, ['profile_image' => $path]);
    }

    public function updateStudentProfilePicture($file): void
    {
        $studentId = Auth::id();
        $profile = $this->profileRepository->findOrFailDoctorProfile($studentId);

        if ($profile?->profile?->profile_image && Storage::disk('public')->exists($profile?->profile?->profile_image)) {
            Storage::disk('public')->delete($profile?->profile?->profile_image);
        }


        $fileName = $studentId . '_Student_' . time() . '.' . $file->getClientOriginalExtension();

        $path = $file->storeAs('student_profile_image', $fileName, 'public');

        $this->profileRepository->updateProfile($studentId, ['profile_image' => $path]);
    }

    public function getProfile(int $userId)
    {
        $user = $this->profileRepository->getUserWithProfile($userId);

        if (!$user) {
            throw new PermissionDeniedException('غير موجود', 'المستخدم غير موجود');
        }

        $profile = $user->profile;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'university_number' => $user->university_number,
            'role' => $user->role,
            'governorate' => $profile?->governorate?->name ?? null,
            'phone_number' => $profile?->phone_number,
            'birth_date' => $profile?->birth_date,
            'student_speciality' => $profile?->student_speciality?->name ?? null,
            'student_status' => $profile?->student_status?->name ?? null,
            'profile_image' => UrlHelper::imageUrl($profile->profile_image),
            'created_at' => $user->created_at->format('Y-m-d'),
        ];
    }

    public function getCurrentUserProfile()
    {
        $user = $this->profileRepository->getCurrentUserWithProfile();

        if (!$user) {
            throw new PermissionDeniedException('غير موجود', 'المستخدم غير موجود');
        }

        $profile = $user->profile;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'university_number' => $user->university_number,
            'role' => $user->role,
            'governorate' => $profile?->governorate?->name ?? null,
            'phone_number' => $profile?->phone_number,
            'birth_date' => $profile?->birth_date,
            'student_speciality' => $profile?->student_speciality?->name ?? null,
            'student_status' => $profile?->student_status?->name ?? null,
            'profile_image' => UrlHelper::imageUrl($profile->profile_image),
            'created_at' => $user->created_at->format('Y-m-d'),
        ];
    }

    public function updateCurrentUserProfile(array $data): void
    {
        $user = Auth::user();

        if (!$user) {
            throw new PermissionDeniedException('غير موجود', 'المستخدم غير موجود');
        }

        $birthDate = Carbon::createFromFormat('d/m/Y', $data['birth_date'])->format('Y-m-d');

        $allowedData = [
            'governorate' => $data['governorate'],
            'phone_number' => $data['phone_number'],
            'birth_date' => $birthDate ,
            'student_speciality' => $data['student_speciality'],
        ];

        $this->profileRepository->updateProfileStudent($user->id, $allowedData);
    }
}
