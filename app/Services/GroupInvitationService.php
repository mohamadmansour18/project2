<?php
namespace App\Services;

use App\Enums\GroupInvitationStatus;
use App\Enums\GroupMemberRole;
use App\Exceptions\InvalidInvitationException;
use App\Exceptions\PermissionDeniedException;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\User;
use App\Repositories\GroupInvitationRepository;
use App\Repositories\GroupMemberRepository;
use App\Repositories\GroupRepository;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class GroupInvitationService
{
    public function __construct(
        protected GroupInvitationRepository $invitationRepo,
        protected GroupMemberRepository $groupMemberRepo,
        protected GroupRepository $groupRepo
    ){ }

    public function send(int $groupId, int $userId, User $inviter): void
    {
        // isLeader؟
        if (!$this->groupMemberRepo->isLeader($groupId, $inviter->id)) {
            throw new PermissionDeniedException(
                'غير مصرح',
                'فقط القائد يمكنه إرسال الدعوات',
                403
            );
        }

        //not me
        if ($inviter->id === $userId) {
            throw new PermissionDeniedException(
                'غير مسموح',
                'لا يمكنك إرسال دعوة لنفسك',
                403
            );
        }

        // isMember
        if ($this->groupMemberRepo->isMember($groupId, $userId)) {
            throw new PermissionDeniedException(
                'عضو موجود',
                'هذا المستخدم هو بالفعل عضو في المجموعة',
                403
            );
        }

        // is Already Invited
        if ($this->invitationRepo->isAlreadyInvited($groupId, $userId)) {
            throw new PermissionDeniedException(
                'دعوة موجودة',
                'تم إرسال دعوة لهذا المستخدم مسبقاً',
                403
            );
        }

        //less than 5 member
        $memberCount = $this->groupRepo->getMemberCount($groupId);
        if ($memberCount >= 5) {
            throw new PermissionDeniedException(
                'عدد الأعضاء ممتلئ',
                'لا يمكن إرسال دعوة، الحد الأقصى لعدد الأعضاء هو 5',
                403
            );
        }

        // create invitation
        $this->invitationRepo->create($groupId, $userId, $inviter->id);
    }

    public function getUserInvitations(User $user): array
    {
        return $this->invitationRepo->getUserPendingInvitations($user->id)
            ->map(fn($invitation) => [
                'id' => $invitation->id,
                'group_id' => $invitation->group_id,
                'invited_user_id' => $invitation->invited_user_id,
                'invited_by_user_id' => $invitation->invited_by_user_id,
                'status' => $invitation->status,
                'group' => [
                    'id' => $invitation->group->id,
                    'name' => $invitation->group->name,
                    'speciality_needed' => $invitation->group->speciality_needed,
                    'image' => $invitation->group->image,
                ],
            ])
            ->toArray();
    }

    public function accept(int $invitationId, User $user): void
    {
        $invitation = $this->invitationRepo->findPendingByIdForUser($invitationId, $user->id);

        if (!$invitation) {
            throw new PermissionDeniedException(
                'غير مسموح',
                'الدعوة غير موجودة أو غير صالحة.',
                403
            );
        }

        $group = $invitation->group;

        if ($group->number_of_members >= 5){
            throw new PermissionDeniedException(
                'غير مسموح',
                'المجموعة ممتلئة بالفعل.',
                403
            );
        }

        $this->groupMemberRepo->create($group->id, $user->id, GroupMemberRole::Member);

        $this->invitationRepo->updateStatus($invitation, GroupInvitationStatus::Accepted);

        $group->increment('number_of_members');
    }

    public function reject(int $invitationId, User $user): void
    {
        $invitation = $this->invitationRepo->findPendingByIdForUser($invitationId, $user->id);

        if (!$invitation) {
            throw new PermissionDeniedException(
                'غير مسموح',
                'الدعوة غير موجودة أو تم التعامل معها سابقاً.',
                403
            );
        }

        $this->invitationRepo->updateStatus($invitation, GroupInvitationStatus::Rejected);
    }

    public function cancel(int $invitationId, User $user): void
    {
        $invitation = $this->invitationRepo->findPendingById($invitationId);

        if (!$invitation) {
            throw new PermissionDeniedException(
                'غير مسموح',
                'الدعوة غير موجودة أو تم التعامل معها سابقاً.',
                403
            );
        }

        // التحقق إن المستخدم الحالي هو القائد
        if (!$this->groupMemberRepo->isLeader($invitation->group_id, $user->id)) {
            throw new PermissionDeniedException(
                'صلاحية مفقودة',
                'فقط قائد المجموعة يمكنه إلغاء الدعوة.',
                403
            );
        }

        $this->invitationRepo->updateStatus($invitation, GroupInvitationStatus::Cancelled);
    }


}

