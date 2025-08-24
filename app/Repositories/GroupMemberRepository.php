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

    public function getLeader(int $groupId)
    {
        return GroupMember::with('user')
            ->where('group_id', $groupId)
            ->where('role', GroupMemberRole::Leader)
            ->first()?->user;
    }


    public function isInAnyGroup(int $userId): bool
    {
        return GroupMember::where('user_id', $userId)->exists();
    }

    public function updateRole(int $groupId, int $userId, GroupMemberRole $role): void
    {
        GroupMember::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->update(['role' => $role]);
    }

    public function getMembersForUserGroup(int $userId)
    {
        $groupMember = GroupMember::where('user_id', $userId)->first();

        if (!$groupMember) return collect();

        return GroupMember::with(['user.profile'])
            ->where('group_id', $groupMember->group_id)
            ->get();
    }

    public function getGroupMemberIds(int $groupId): array
    {
        return GroupMember::where('group_id', $groupId)
            ->pluck('user_id')
            ->toArray();
    }

    public function getMembersWithProfile(int $groupId)
    {
        return GroupMember::with(['user.profile'])
            ->where('group_id', $groupId)
            ->get();
    }



}
