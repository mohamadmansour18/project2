<?php
namespace App\Repositories;

use App\Enums\GroupMemberRole;
use App\Models\JoinRequest;
use App\Enums\JoinRequestStatus;

class JoinRequestRepository
{
    public function hasPendingRequest(int $groupId, int $userId): bool
    {
        return JoinRequest::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->where('status', JoinRequestStatus::Pending)
            ->exists();
    }

    public function hasPendingRequestSixthStudent(int $groupId, int $userId): bool
    {
        return JoinRequest::where('group_id', $groupId)
            ->where('user_id', $userId)
            ->where('status', JoinRequestStatus::PendingLeader)
            ->exists();
    }

    public function create(int $groupId, int $userId, ?string $description = null): JoinRequest
    {
        return JoinRequest::create([
            'group_id' => $groupId,
            'user_id' => $userId,
            'status' => JoinRequestStatus::Pending,
        ]);
    }

    public function createSixthMemberRequest(int $groupId, int $userId, ?string $description = null): JoinRequest
    {
        return JoinRequest::create([
            'group_id'    => $groupId,
            'user_id'     => $userId,
            'status'      => JoinRequestStatus::PendingLeader,
            'description' => $description,
        ]);
    }


    public function getPendingForGroupWithUserAndProfile(int $groupId)
    {
        return JoinRequest::with(['user.profile'])
            ->where('group_id', $groupId)
            ->whereIn('status', [
                JoinRequestStatus::Pending,
                JoinRequestStatus::PendingLeader
            ])
            ->get();
    }

    public function findPendingById(int $id)
    {
        return JoinRequest::with('group')->where('id', $id)
            ->where('status', JoinRequestStatus::Pending)
            ->first();
    }

    public function findPendingByGroupAndUser(int $groupId, int $userId)
    {
        return JoinRequest::with('group')
            ->where('group_id', $groupId)
            ->where('user_id', $userId)
            ->where('status', JoinRequestStatus::Pending)
            ->first();
    }

    public function findPendingByIdSixth(int $id)
    {
        return JoinRequest::with('group')->where('id', $id)
            ->where('status', JoinRequestStatus::PendingLeader)
            ->first();
    }

    public function findPendingByIdSixthHead(int $id)
    {
        return JoinRequest::with('group')->where('id', $id)
            ->where('status', JoinRequestStatus::PendingHead)
            ->first();
    }

    public function getUserPendingRequestsWithGroup(int $userId)
    {
        return JoinRequest::with('group')
            ->where('user_id', $userId)
            ->where('status', JoinRequestStatus::Pending)
            ->get();
    }

// JoinRequestRepository.php
// JoinRequestRepository.php
    public function getPendingForLeader(int $groupId)
    {
        return JoinRequest::with(['user.profile', 'group'])
            ->where('status', JoinRequestStatus::PendingLeader) // أو Pending حسب حالتك
            ->where('group_id', $groupId)
            ->get();
    }


    // الطلبات المعلقة على رئيس القسم
    public function getPendingForHead()
    {
        return JoinRequest::with(['user.profile', 'group'])
            ->where('status', JoinRequestStatus::PendingHead)
            ->get();
    }

    public function updateStatus(JoinRequest $request, JoinRequestStatus $status): void
    {
        $request->update([
            'status' => $status,
            'reviewed_at' => now(),
        ]);
    }


}
