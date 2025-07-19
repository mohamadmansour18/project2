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

    public function addMember(Group $group, int $userId,  GroupMemberRole $role = GroupMemberRole::Member): GroupMember
    {
        return GroupMember::create([
            'group_id' => $group->id,
            'user_id' => $userId,
            'role' => $role
        ]);
    }

    public function invite(Group $group, int $invitedUserId, int $invitedBy): GroupInvitation
    {
        return GroupInvitation::create([
            'group_id' => $group->id,
            'invited_user_id' => $invitedUserId,
            'invited_by_user_id' => $invitedBy,
            'status' => GroupInvitationStatus::Pending
        ]);
    }
}
