<?php

namespace App\Repositories;

use App\Models\InterviewPeriod;

class InterviewPeriodRepository
{
    public function create(array $data): InterviewPeriod
    {
        return InterviewPeriod::create([
            'start_date' => $data['start_date'],
            'end_date'   => $data['end_date'],
            'days'       => $data['days'],
        ]);
    }

    public function update(InterviewPeriod $interviewPeriod , array $data): InterviewPeriod
    {
        $interviewPeriod->update($data);
        return $interviewPeriod;
    }

    public function findOrFail(int $periodId): InterviewPeriod
    {
        return InterviewPeriod::findOrFail($periodId);
    }

    public function forceDelete(InterviewPeriod $interviewPeriod): void
    {
        $interviewPeriod->forceDelete();
    }

    public function getCurrentYearInterview(): ?InterviewPeriod
    {
        $currentYear = now()->year;
        return InterviewPeriod::whereYear('created_at' , $currentYear)->first();
    }
}
