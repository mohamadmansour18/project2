<?php

namespace App\Repositories;

use App\Models\InterviewSchedule;

class InterviewScheduleRepository
{
    public function countInterviewGroupsForCurrentYear()
    {
        return InterviewSchedule::whereYear('created_at' , now()->year)
            ->distinct('group_id')
            ->count('group_id');
    }
}
