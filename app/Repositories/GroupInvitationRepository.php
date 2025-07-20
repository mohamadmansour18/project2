<?php

namespace App\Repositories;


use App\Models\GroupInvitation;
use App\Enums\GroupInvitationStatus;

class GroupInvitationRepository
{
    public function createInvitation(int $groupId, int $invitedUserId, int $invitedByUserId): GroupInvitation
    {
        return GroupInvitation::create([
            'group_id' => $groupId,
            'invited_user_id' => $invitedUserId,
            'invited_by_user_id' => $invitedByUserId,
            'status' => GroupInvitationStatus::Pending,
        ]);
    }

    public function userAlreadyInvited(int $groupId, int $userId): bool
    {
        return GroupInvitation::where('group_id', $groupId)
            ->where('invited_user_id', $userId)
            ->where('status', GroupInvitationStatus::Pending)
            ->exists();
    }

    public function getInvitationsForUser(int $userId)
    {
        return GroupInvitation::with('group')
            ->where('invited_user_id', $userId)
            ->where('status', GroupInvitationStatus::Pending)
            ->get();
    }

}
