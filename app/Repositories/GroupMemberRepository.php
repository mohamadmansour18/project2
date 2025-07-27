<?php

namespace App\Repositories;

use App\Models\GroupMember;
use App\Enums\GroupMemberRole;

class GroupMemberRepository
{
    public function create(int $groupId, int $userId, GroupMemberRole $role): GroupMember
    {
        return GroupMember::create([
            'group_id' => $groupId,
            'user_id' => $userId,
            'role' => $role,
        ]);
    }

    public function isMember(int $groupId, int $userId): bool
    {
        return GroupMember::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->exists();
    }

    public function isLeader(int $groupId, int $userId): bool
    {
        return GroupMember::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->where('role', GroupMemberRole::Leader)
            ->exists();
    }

    public function isInAnyGroup(int $userId): bool
    {
        return GroupMember::where('user_id', $userId)->exists();
    }

}
