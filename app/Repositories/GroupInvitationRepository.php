<?php

namespace App\Repositories;

use App\Models\GroupInvitation;
use App\Enums\GroupInvitationStatus;
use Illuminate\Database\Eloquent\Collection;

class GroupInvitationRepository
{
    public function create(int $groupId, int $invitedUserId, int $invitedByUserId): GroupInvitation
    {
        return GroupInvitation::create([
            'group_id' => $groupId,
            'invited_user_id' => $invitedUserId,
            'invited_by_user_id' => $invitedByUserId,
            'status' => GroupInvitationStatus::Pending,
        ]);
    }

    public function isAlreadyInvited(int $groupId, int $userId): bool
    {
        return GroupInvitation::where('group_id', $groupId)
            ->where('invited_user_id', $userId)
            ->where('status', GroupInvitationStatus::Pending)
            ->exists();
    }

    public function getUserPendingInvitations(int $userId): Collection
    {
        return GroupInvitation::with('group')
            ->where('invited_user_id', $userId)
            ->where('status', GroupInvitationStatus::Pending)
            ->get();
    }

    public function findPendingByIdForUser(int $invitationId, int $userId)
    {
        return GroupInvitation::with('group')
            ->where('id', $invitationId)
            ->where('invited_user_id', $userId)
            ->where('status', GroupInvitationStatus::Pending)
            ->first();
    }
    public function findPendingByInviterAndInvitedInUserGroup(int $inviterId, int $invitedUserId, int $groupId): ?GroupInvitation
    {
        return GroupInvitation::where('invited_by_user_id', $inviterId)
            ->where('invited_user_id', $invitedUserId)
            ->where('group_id', $groupId)
            ->where('status', GroupInvitationStatus::Pending->value) // لاحظ استخدام ->value
            ->first();
    }






    public function updateStatus(GroupInvitation $invitation, GroupInvitationStatus $status): void
    {
        $invitation->update(['status' => $status]);
    }

    public function findPendingById(int $invitationId)
    {
        return GroupInvitation::with('group')
            ->where('id', $invitationId)
            ->where('status', GroupInvitationStatus::Pending)
            ->first();
    }

    public function getGroupInvitationsWithUser(int $group): Collection
    {
        return GroupInvitation::with(['invitedUser.profile'])
            ->where('group_id', $group)
            ->where('status', GroupInvitationStatus::Pending)
            ->get();
    }

}
