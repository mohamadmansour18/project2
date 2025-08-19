<?php

namespace App\Repositories;

use App\Models\InterviewCommittee;
use App\Models\ProjectGrade;
use Carbon\Carbon;

class InterviewCommitteeRepository
{
    public function isDoctorInInterviewCommitteeThisYear(int $doctorId): bool
    {
        return InterviewCommittee::query()->whereYear('created_at' , now()->year)
            ->where(function ($query) use ($doctorId) {
                $query->where('supervisor_id' , $doctorId)
                    ->orWhere('member_id' , $doctorId);
            })
            ->exists();
    }

    public function getDoctorCommitteeGroups(int $doctorId)
    {
        $currentYear = Carbon::now()->year ;

        $committee = InterviewCommittee::query()
            ->where(function ($query) use ($doctorId) {
                $query->where('supervisor_id' , $doctorId)
                      ->orWhere('member_id' , $doctorId);
            })
            ->first();

        if(!$committee)
        {
            return collect();
        }

        $schedules = $committee->schedules()
            ->whereYear('interview_date' , $currentYear)
            ->with(['group.projectForms' , 'group.projectForm2'])
            ->orderBy('interview_date') //sorting by date and if two row equally sort by time
            ->orderBy('interview_time')
            ->get();

        //retrieve grade with every group committee if group do interview
        $schedules->map(function ($schedule) use ($committee) {
            $grade = ProjectGrade::where('committee_id' , $committee->id)
                                 ->where('group_id' , $schedule->group_id)
                                 ->first();

            $schedule->total_grade = $grade ? '100/' . $grade->total_grade : null;
            return $schedule ;
        });

        return $schedules;
    }

    public function searchDoctorCommitteeGroups(int $doctorId , string $searchKey)
    {
        $currentYear = now()->year;

        $committee = InterviewCommittee::query()
            ->where(function ($query) use ($doctorId) {
                $query->where('supervisor_id', $doctorId)
                    ->orWhere('member_id', $doctorId);
            })
            ->first();

        if (!$committee) {
            return collect();
        }

        $schedules = $committee->schedules()
            ->whereYear('interview_date', $currentYear)
            ->whereHas('group', function ($q) use ($searchKey) {
                $q->where('name', 'like', $searchKey . '%');
            })
            ->with(['group.projectForms', 'group.projectForm2'])
            ->orderBy('interview_date')
            ->orderBy('interview_time')
            ->get();

        $schedules->map(function ($schedule) use ($committee) {
            $grade = ProjectGrade::query()
                ->where('committee_id', $committee->id)
                ->where('group_id', $schedule->group_id)
                ->first();

            $schedule->total_grade = $grade ? '100/' . $grade->total_grade : null;
            return $schedule;
        });

        return $schedules;
    }
}
