<?php

namespace App\Repositories;

use App\Enums\ProfileStudentStatus;
use App\Models\InterviewCommittee;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class ProfileRepository
{
    public function createProfile(array $data)
    {
        return Profile::create($data);
    }

    public function updateProfileForSpecificUser(User $user, array $data): void
    {
        optional($user->profile)->update($data);
    }

    public function updateStudentStatus(array $studentIds , ProfileStudentStatus $status): void
    {
        Profile::whereIn('user_id', $studentIds)
            ->update([
                'student_status' => $status
            ]);
    }

    public function findOrFailDoctorProfile(int $doctorId)
    {
        return User::with('profile')->findOrFail($doctorId);
    }

    public function updateProfile(int $doctorId, array $data): void
    {
        $profile = Profile::where('user_id' , $doctorId)->first();

        $profile->update($data);
    }

    public function isDoctorInCommitteeCurrentYear(int $doctorId): bool
    {
        return InterviewCommittee::query()
            ->whereYear('created_at' , now()->year)
            ->where(function ($query) use ($doctorId) {
                $query->where('supervisor_id', $doctorId)
                      ->orWhere('member_id', $doctorId);

            })
            ->exists();
    }

}
