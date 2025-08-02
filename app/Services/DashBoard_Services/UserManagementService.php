<?php

namespace App\Services\DashBoard_Services;

use App\Helpers\UrlHelper;
use App\Repositories\UserRepository;

class UserManagementService
{

    public function __construct(
        protected UserRepository $userRepository,
    )
    {}

    public function getAllDoctorsDetailed(): array
    {
        $doctors = $this->userRepository->getAllDoctorsWithProfile();

        return $doctors->map(function ($doctor) {

            $profile = optional($doctor->profile);

            return [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'email' => $doctor->email,
                'governorate' => $profile->governorate->value,
                'phone_number' => $profile->phone_number,
                'Registration_date' => $profile->created_at->toDateString(),
                'profile_image' => UrlHelper::imageUrl($profile->profile_image),
            ];
        });
    }

    public function searchDoctorByName(string $name): array
    {
        $doctors = $this->userRepository->searchDoctorByName($name);

        return $doctors->map(function($doctor){

            $profile = optional($doctor->profile);
            return [
                'id' => $doctor->id ,
                'name' => $doctor->name,
                'email' => $doctor->email,
                'governorate' => $profile->governorate->value,
                'phone_number' => $profile->phone_number,
                'created_at' => $doctor->created_at->toDateString(),
                'profile_image' => UrlHelper::imageUrl($profile->profile_image)
            ];
        });
    }

}
