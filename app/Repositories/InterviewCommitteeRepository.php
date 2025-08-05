<?php

namespace App\Repositories;

use App\Models\InterviewCommittee;

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
}
