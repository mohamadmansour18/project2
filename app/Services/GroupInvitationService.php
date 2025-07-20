<?php

namespace App\Services;

use App\Enums\GroupMemberRole;
use App\Models\Group;
use App\Models\User;
use App\Repositories\GroupInvitationRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class GroupInvitationService
{
    protected GroupInvitationRepository $invitationRepo;

    public function __construct(GroupInvitationRepository $invitationRepo)
    {
        $this->invitationRepo = $invitationRepo;
    }

    public function sendInvitation(int $groupId, int $userId, User $invitedBy): void
    {
        $group = Group::with(['members' => fn($query) => $query->where('user_id', $invitedBy->id)])
            ->findOrFail($groupId);

        $member = $group->members->first();

        if (!$member || $member->role !== GroupMemberRole::Leader) {
            throw new AccessDeniedHttpException("فقط القائد يمكنه إرسال الدعوات.");
        }

        if (!$this->invitationRepo->userAlreadyInvited($groupId, $userId)) {
            $this->invitationRepo->createInvitation($groupId, $userId, $invitedBy->id);
        }
    }

    public function getUserInvitations(User $user)
    {
        return $this->invitationRepo->getInvitationsForUser($user->id);
    }

}
