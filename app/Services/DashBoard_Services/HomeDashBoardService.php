<?php

namespace App\Services\DashBoard_Services;
use App\Repositories\GroupRepository;
use App\Repositories\ProjectFormRepository;
use App\Repositories\UserRepository;

class HomeDashBoardService
{
    public function __construct(
        protected UserRepository $userRepository ,
        protected GroupRepository $groupRepository,
        protected ProjectFormRepository $projectFormRepository
    )
    {}

    public function getHomeCurdCurrentYearStats(): array
    {
        $currentYear = now()->year ;
        $years = range($currentYear , $currentYear - 3);

        $result = [];
        foreach ($years as $year)
        {
            $result[$year]=[
                'students_count' => $this->userRepository->getStudentCountForCurrentYearDynamic($year),
                'doctor_count' => $this->userRepository->getDoctorCountForCurrentYearDynamic($year),
                'ideas_count' => $this->projectFormRepository->getApprovedIdeasCountForCurrentYearDynamic($year),
                'groups_count' => $this->groupRepository->getGroupsCountForCurrentYearDynamic($year),
            ];
        }

        return $result;
    }

    public function getAllDoctorsForAdminHomePage(): array
    {
        $doctors = $this->userRepository->getDoctorSpecificDataForAdminHomePage();

        return $doctors->map(function($doctor){

            $profile = optional($doctor->profile);

            return [
                'id' => $doctor->id ,
                'name' => $doctor->name ,
                'profile_image' => $profile->profile_image
            ];
        });
    }
}
