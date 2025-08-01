<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{

    public function __construct(
        private UserRepository $userRepository,
        private ImageService $imageService
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
                'profile_image' => $this->imageService->getFullUrl($profileImagePath),
            ];
        })->toArray();
    }

}
