<?php

namespace App\Services;

use App\Enums\ProjectFormStatus;
use App\Helpers\UrlHelper;
use App\Repositories\InterviewCommitteeRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class InterviewCommitteeService
{
    public function __construct(
        protected InterviewCommitteeRepository $interviewCommitteeRepository
    )
    {}

    public function getDoctorCommitteeGroupsData()
    {
        $doctorId = Auth::id();

        $schedules = $this->interviewCommitteeRepository->getDoctorCommitteeGroups($doctorId);

        return $schedules->map(function ($schedule) {
            $group = optional($schedule->group);

            return [
                'id' => $group->id,
                'name' => $group->name,
                'group_image' => UrlHelper::imageUrl($group->image),
                'form1' => $this->checkForm1($group),
                'form2' => $this->checkForm2($group),
                'interview_date' => $schedule->interview_date ? $schedule->interview_date->toDateString() : null,
                'interview_time' => $schedule->interview_time ? $this->formatTime($schedule->interview_time) : null,
                'total_grade' => $schedule->total_grade,
            ];
        });
    }

    public function searchDoctorCommitteeGroupsData(string $searchKey)
    {
        $doctorId = Auth::id();

        $schedules = $this->interviewCommitteeRepository->searchDoctorCommitteeGroups($doctorId , $searchKey);

        return $schedules->map(function ($schedule) {
            $group = optional($schedule->group);

            return [
                'id' => $group->id,
                'name' => $group->name,
                'group_image' => UrlHelper::imageUrl($group->image),
                'form1' => $this->checkForm1($group),
                'form2' => $this->checkForm2($group),
                'interview_date' => $schedule->interview_date ? $schedule->interview_date->toDateString() : null,
                'interview_time' => $schedule->interview_time ? $this->formatTime($schedule->interview_time) : null,
                'total_grade' => $schedule->total_grade,
            ];
        });
    }

    private function checkForm1($group): ?string
    {
        $form1 = $group->projectForm()->whereIn('status' , [ProjectFormStatus::Approved->value , ProjectFormStatus::Pending->value])->first();

        return $form1 ? "# استمارة 1" : null ;
    }

    private function checkForm2($group): ?string
    {
        $form2 = $group->projectForm2()->first();

        return $form2 ?"# استمارة 2" : null ;
    }

    private function formatTime(Carbon $time): string
    {

        $formatted = $time->format("h:i");
        $suffix = $time->format('A') === 'AM' ? 'ص' : 'م' ;
        return $formatted . $suffix ;
    }
}
