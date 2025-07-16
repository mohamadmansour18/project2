<?php

namespace App\Services;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementType;
use App\Models\FormSubmissionPeriod;
use App\Repositories\AnnouncementRepository;
use App\Repositories\FormSubmissionPeriodRepository;
use App\Repositories\GroupRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class HomeMobileService
{

    public function __construct(
        protected FormSubmissionPeriodRepository $formRepository ,
        protected AnnouncementRepository $announcementRepository ,
        protected UserRepository $userRepository,
        protected GroupRepository $groupRepository
    ) {}

    public function getAllFormPeriods(FormSubmissionPeriodRepository $formRepository): array
    {
        return Cache::rememberForever('home_form_periods', function () {
            $forms = $this->formRepository->getAllCurrentYearForms();

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

    public function getHomeStatistics(): array
    {

        return [
            'doctorsCount' => $this->userRepository->getDoctorCount(),
            'groupsCount' => $this->groupRepository->getGroupsCountForCurrentYear(),
            'studentsCount' => $this->userRepository->getStudentCountForCurrentYear(),
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
