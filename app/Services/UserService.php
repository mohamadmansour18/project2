<?php

namespace App\Services;

use App\Enums\ProfileStudentSpeciality;
use App\Enums\ProfileStudentStatus;
use App\Helpers\UrlHelper;
use App\Repositories\UserRepository;

class UserService
{

    public function __construct(
        protected UserRepository $userRepository,
    ) {}

    public function getStudentsForCurrentYear(): array
    {
        return $this->userRepository->getStudentsForCurrentYear()->map(function ($user) {
            $profile = optional($user->profile);
            $profileImagePath = $profile->profile_image;

            return [
                'id' => $user->id,
                'name' => $user->name,
                'student_status' => $profile->student_status?->value,
                'student_speciality' => $profile->student_speciality?->value,
                'profile_image' => UrlHelper::imageUrl($profileImagePath),
            ];
        })->toArray();
    }

    public function getAllDoctors()
    {
        return $this->userRepository->getAllDoctors();
    }

    public function getUserInfo(int $userId): array
    {
        $user = $this->userRepository->findUserWithProfileAndGroups($userId);

        $hasGroup = $user->groupMembers && $user->groupMember->isNotEmpty();

        return [
            'id' => $user->id,
            'profile_image' => UrlHelper::imageUrl($user->profile->profile_image),
            'name' => $user->name,
            'created_at' => $user->created_at->format('Y-m-d') . " : تاريخ التسجيل",
            'status' => $user && $user->profile?->student_status ? $this->formatStatus($user->profile->student_status->value) : '',
            'group_status' => $hasGroup ? 'ضمن غروب' : 'ليس ضمن غروب',
            'in_group' => $hasGroup,
            'governorate' => $user->profile->governorate ?? '',
            'email' => $user->email,
            'speciality' => $user && $user->profile?->student_speciality ? $this->formatSpeciality($user->profile->student_speciality->value) : '',



        ];
    }

    private function formatStatus(?string $status): ?string
    {
        if($status == ProfileStudentStatus::Fourth_Year->value)
        {
            return 'سنة رابعة';
        }
        if($status == ProfileStudentStatus::Successful->value)
        {
            return 'ناجح في المشروع';
        }
        return 'اعادة مشروع';
    }

    private function formatSpeciality(?string $speciality): ?string
    {
        switch ($speciality){
            case ProfileStudentSpeciality::Backend->value:
                $value = "باك ايند #";
                break;

            case ProfileStudentSpeciality::Front_Mobile->value :
                $value = "فرونت موبايل #";
                break;

            case ProfileStudentSpeciality::Front_Web->value :
                $value = "فرونت ويب #";
                break;

            default :
                $value = '';
        }
        return $value;
    }

}
