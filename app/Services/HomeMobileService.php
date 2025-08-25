<?php

namespace App\Services;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementType;
use App\Models\FormSubmissionPeriod;
use App\Repositories\AnnouncementRepository;
use App\Repositories\FormSubmissionPeriodRepository;
use App\Repositories\GroupRepository;
use App\Repositories\InterviewScheduleRepository;
use App\Repositories\ProjectForm2Repository;
use App\Repositories\ProjectFormRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class HomeMobileService
{

    public function __construct(
        protected FormSubmissionPeriodRepository $formSubmissionPeriodRepository ,
        protected AnnouncementRepository $announcementRepository ,
        protected UserRepository $userRepository,
        protected GroupRepository $groupRepository,
        protected InterviewScheduleRepository $interviewScheduleRepository ,
        protected ProjectFormRepository $projectFormRepository ,
        protected ProjectForm2Repository $projectForm2Repository
    ) {}

    public function getAllFormPeriods(): array
    {
        return Cache::rememberForever('home_form_periods', function () {
            $forms = $this->formSubmissionPeriodRepository->getAllCurrentYearForms();

            return [
                'form1' => $this->formatForm($forms->get('form1')),
                'form2' => $this->formatForm($forms->get('form2')),
                'interview' => $this->formatForm($forms->get('interviews')),
            ];
        });

    }

    public function getAnnouncementStatistics(): array
    {
        $data = $this->announcementRepository->getCurrentYearAnnouncements();

        $imageAnnouncements = $data->where('type' , AnnouncementType::Image);
        $fileAnnouncements = $data->where('type' , AnnouncementType::File);

        return [
            'imageAnnouncements' => [
                'count' => $imageAnnouncements->count(),
                'administrativeCount' =>  "اعلان اداري " . $imageAnnouncements->where('audience' , AnnouncementAudience::Professors)->count()
            ],
            'fileAnnouncements' => [
                'count' => $fileAnnouncements->count(),
                'administrativeCount' => 'اعلان اداري ' . $fileAnnouncements->where('audience' , AnnouncementAudience::Professors)->count()
            ]
        ];
    }

    public function getStudentAnnouncementStatistics(): array
    {
        $data = $this->announcementRepository->getCurrentYearAnnouncements();

        $imageAnnouncements = $data->where('type', AnnouncementType::Image)
            ->where('audience', AnnouncementAudience::All);

        $fileAnnouncements = $data->where('type', AnnouncementType::File)
            ->where('audience', AnnouncementAudience::All);

        return [
            'imageAnnouncements' => [
                'count' => $imageAnnouncements->count(),
            ],
            'fileAnnouncements' => [
                'count' => $fileAnnouncements->count(),
            ]
        ];
    }


    public function getHomeStatistics(): array
    {

        return [
            'doctorsCount' => $this->userRepository->getDoctorCount(),
            'groupsCount' => $this->groupRepository->getGroupsCountForCurrentYear(),
            'studentsCount' => $this->userRepository->getStudentCountForCurrentYear(),
        ];
    }

    public function getGroupStatistics(): array
    {
        return [
            'studentsCount'=> $this->userRepository->getStudentCountForCurrentYear() . ' طالب' ,
            'form1' => $this->projectFormRepository->countForm1GroupsForCurrentYear() . ' غروبات' ,
            'form2' => $this->projectForm2Repository->countForm2GroupsForCurrentYear() . ' غروبات' ,
            'interviews' => $this->interviewScheduleRepository->countInterviewGroupsForCurrentYear() . ' غروبات'
        ];
    }

    //////////////////////////--------< Helpers >--------/////////////////////////////
    private function formatForm(?FormSubmissionPeriod $period): array
    {

        if(!$period)
        {
            return [
                'start' => '--:--:--' ,
                'end' => '--:--:--' ,
                'remaining' => '--' ,
            ];
        }

        return [
            'start' => $period->start_date->format('Y-m-d'),
            'end' => $period->end_date->format('Y-m-d'),
            'remaining' => $this->formatRemainingTime($period->end_date)
        ];
    }

    private function formatRemainingTime(Carbon $endDate): string
    {
        $now = now();

        if($now->greaterThanOrEqualTo($endDate)){
            return '--';
        }

        $diffInMinutes = $now->diffInMinutes($endDate);
        $diffInHours = $now->diffInHours($endDate);
        $diffInDays = $now->diffInDays($endDate);
        $diffInMonths = $now->diffInMonths($endDate);

        if($diffInDays > 30)
        {
            return  $diffInMonths . 'أشهر';
        }

        if($diffInDays >= 1)
        {
            return  $diffInDays . 'يوم';
        }

        if($diffInHours >= 1)
        {
            return  $diffInHours . 'ساعة';
        }

        return  $diffInMinutes .  'دقيقة';
    }
}
