<?php

namespace App\Services\DashBoard_Services;
use App\Helpers\UrlHelper;
use App\Repositories\GroupRepository;
use App\Repositories\InterviewCommitteeRepository;
use App\Repositories\InterviewPeriodRepository;
use App\Repositories\ProjectFormRepository;
use App\Repositories\UserRepository;

class HomeDashBoardService
{
    public function __construct(
        protected UserRepository $userRepository ,
        protected GroupRepository $groupRepository,
        protected ProjectFormRepository $projectFormRepository,
        protected InterviewCommitteeRepository $interviewCommitteeRepository,
        protected InterviewPeriodRepository $interviewPeriodRepository,
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

        $results = $doctors->map(function($doctor){

            $profile = optional($doctor->profile);
            return [
                'id' => $doctor->id ,
                'name' => $doctor->name ,
                'profile_image' => UrlHelper::imageUrl($profile->profile_image)
            ];
        });

        return ['data' => $results];
    }

    public function getCommitteesAndPeriods(): array
    {
        $committees = $this->interviewCommitteeRepository->getCommitteesForCurrentYear()->map(function($committee){
            return [
                'firstName'   => $committee->adminSupervisor?->name ?? 'لايوجد',
                'secondName'  => $committee->adminMember?->name ?? 'لايوجد',
                'firstImage'  => UrlHelper::imageUrl($committee->adminSupervisor?->profile?->profile_image) ?? null,
                'secondImage' => UrlHelper::imageUrl($committee->adminMember?->profile?->profile_image) ?? null,
                'supervisor'  => $committee->adminSupervisor?->name ?? 'لايوجد',
            ];
        })->values();

        $period = $this->interviewPeriodRepository->getCurrentYearInterview();

        $periodSelect = [
            'start_date' => $period->start_date,
            'end_date' => $period->end_date,
            'days' => $period->days
        ];

        return [
            'committees'  => $committees ?? [],
            'interview_periods' => $periodSelect ?? [],
        ];
    }
}
