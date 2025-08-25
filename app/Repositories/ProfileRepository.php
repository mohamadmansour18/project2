<?php

namespace App\Repositories;

use App\Enums\ProfileStudentStatus;
use App\Models\Profile;
use App\Models\User;

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
}
