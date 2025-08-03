<?php

namespace App\Services\DashBoard_Services;

use App\Enums\UserRole;
use App\Helpers\UrlHelper;
use App\Repositories\ProfileRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;

class UserManagementService
{

    public function __construct(
        protected UserRepository $userRepository,
        protected ProfileRepository $profileRepository,
    )
    {}

    public function getAllDoctorsDetailed(): array
    {
        $doctors = $this->userRepository->getAllDoctorsWithProfile();

        $results = $doctors->map(function ($doctor) {

            $profile = optional($doctor->profile);

            return [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'email' => $doctor->email,
                'governorate' => $profile->governorate,
                'phone_number' => $profile->phone_number,
                'Registration_date' => $profile->created_at->toDateString(),
                'profile_image' => UrlHelper::imageUrl($profile->profile_image),
            ];
        });

        return ['data' => $results];
    }

    public function searchDoctorByName(string $name): array
    {
        $doctors = $this->userRepository->searchDoctorByName($name);

        $results = $doctors->map(function($doctor){

            $profile = optional($doctor->profile);
            return [
                'id' => $doctor->id ,
                'name' => $doctor->name,
                'email' => $doctor->email,
                'governorate' => $profile->governorate,
                'phone_number' => $profile->phone_number,
                'created_at' => $doctor->created_at->toDateString(),
                'profile_image' => UrlHelper::imageUrl($profile->profile_image)
            ];
        });

        return ['data' => $results];
    }

    public function sortDoctors(?string $sortValue): array
    {
        $doctors = $this->userRepository->getSortDoctors($sortValue);

        $result = $doctors->map(function($doctor){
            $profile = optional($doctor->profile);

            return [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'email' => $doctor->email,
                'governorate' => $profile->governorate,
                'phone_number' => $profile->phone_number,
                'created_at' => $doctor->created_at->toDateString(),
                'profile_image' => UrlHelper::imageUrl($profile->profile_image)
            ];
        });

        return ['data' => $result];

    }

    public function insertDoctor(array $data): void
    {
        DB::transaction(function () use ($data) {

            $user = $this->userRepository->createUser([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => UserRole::Doctor,
            ]);

            $imagePath = null ;

            if(!empty($data['profile_image']))
            {
                $image = $data['profile_image'];
                $safePath = $user->id . '_Doctor_' . time() . '.' . $image->getClientOriginalExtension();

                $imagePath = $image->storeAs(
                    'doctor_profile_image' ,
                    $safePath,
                    'public'
                );
            }

            $this->profileRepository->createProfileWithImage($user->id , $imagePath);
        });
    }

}
