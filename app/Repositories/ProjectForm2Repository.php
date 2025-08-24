<?php

namespace App\Repositories;

use App\Enums\GroupMemberRole;
use App\Models\ProjectForm;
use App\Models\ProjectForm2;

class ProjectForm2Repository
{
    public function countForm2GroupsForCurrentYear()
    {
        return ProjectForm2::whereYear('created_at' , now()->year)
            ->distinct('group_id')
            ->count('group_id');
    }

    public function create(array $data): ProjectForm2
    {
        return ProjectForm2::create($data);
    }

    public function existsForGroup(int $groupId, int $leaderId): bool
    {
        return ProjectForm2::where('group_id', $groupId)
            ->whereHas('group.members', function ($query) use ($leaderId) {
                $query->where('user_id', $leaderId)
                    ->where('role', GroupMemberRole::Leader);
            })
            ->exists();
    }

    public function getFormByGroupId(int $groupId)
    {
        return ProjectForm2::query()->where('group_id' , $groupId)->first();
    }
}
