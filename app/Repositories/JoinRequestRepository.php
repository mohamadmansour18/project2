<?php
namespace App\Repositories;

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

    public function create(int $groupId, int $userId, ?string $description = null): JoinRequest
    {
        return JoinRequest::create([
            'group_id' => $groupId,
            'user_id' => $userId,
            'status' => JoinRequestStatus::Pending,
        ]);
    }

    public function getPendingForGroupWithUserAndProfile(int $groupId)
    {
        return JoinRequest::with(['user.profile'])
            ->where('group_id', $groupId)
            ->where('status', JoinRequestStatus::Pending)
            ->get();
    }

    public function findPendingById(int $id)
    {
        return JoinRequest::with('group')->where('id', $id)
            ->where('status', JoinRequestStatus::Pending)
            ->first();
    }

    public function getUserPendingRequestsWithGroup(int $userId)
    {
        return JoinRequest::with('group')
            ->where('user_id', $userId)
            ->where('status', JoinRequestStatus::Pending)
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
