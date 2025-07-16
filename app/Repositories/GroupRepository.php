<?php

namespace App\Repositories;

use App\Models\Group;

class GroupRepository
{
    public function getGroupsCountForCurrentYear(): int
    {
        return Group::query()->whereYear('created_at', now()->year)->count();
    }
}
