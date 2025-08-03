<?php

namespace App\Repositories;

use App\Models\Profile;

class ProfileRepository
{
    public function createProfileWithImage(int $userId , ?string $imagePath = null): Profile
    {
        return Profile::create([
            'user_id' => $userId ,
            'profile_image' => $imagePath,
        ]);
    }
}
