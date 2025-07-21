<?php

namespace App\Repositories;

use App\Enums\GroupMemberRole;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\GroupMember;
use App\Enums\GroupInvitationStatus;

class GroupRepository
{
    public function getGroupsCountForCurrentYear(): int
    {
        return Group::query()->whereYear('created_at', now()->year)->count();
    }

    public function create(array $data): Group
    {
        return Group::create($data);
    }

    public function update(Group $group, array $data): Group
    {
        $group->update($data);
        return $group;
    }

    public function getById(int $id): ?Group
    {
        return Group::find($id);
    }


    public function getMemberCount(int $groupId): int
    {
        $group = $this->getById($groupId);
        return $group ? $group->number_of_members : 0;
    }
}
