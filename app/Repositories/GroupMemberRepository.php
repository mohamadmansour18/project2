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
}
