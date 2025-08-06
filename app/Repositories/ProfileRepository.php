<?php

namespace App\Repositories;

use App\Models\Profile;

class ProfileRepository
{
    public function createProfile(array $data)
    {
        return Profile::create($data);
    }

}
