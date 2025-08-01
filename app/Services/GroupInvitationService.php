<?php
namespace App\Services;

use App\Enums\GroupInvitationStatus;
use App\Enums\GroupMemberRole;
use App\Enums\UserRole;
use App\Exceptions\PermissionDeniedException;
use App\Models\User;
use App\Repositories\GroupInvitationRepository;
use App\Repositories\GroupMemberRepository;
use App\Repositories\GroupRepository;
use App\Repositories\UserRepository;

class GroupInvitationService
{
    public function __construct(
        protected GroupInvitationRepository $invitationRepo,
        protected GroupMemberRepository $groupMemberRepo,
        protected GroupRepository $groupRepo,
        protected UserRepository $userRepo,
        protected ImageService $imageService
    ){ }

    public function send(int $groupId, int $userId, User $inviter): void
    {
        //not me
        if ($inviter->id === $userId) {
            throw new PermissionDeniedException(
                'غير مسموح',
                'لا يمكنك إرسال دعوة لنفسك',
                403
            );
        }

        //just student
        $targetUser = $this->userRepo->findById($userId);
        if (!$targetUser || $targetUser->role !== UserRole::Student) {
            throw new PermissionDeniedException(
                'صلاحية غير مناسبة',
                'يمكن فقط دعوة الطلاب إلى المجموعات',
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
            ->map(function ($invitation) {
                $groupImagePath = $invitation->group->image;

                return [
                    'id' => $invitation->id,
                    'group_id' => $invitation->group_id,
                    'invited_user_id' => $invitation->invited_user_id,
                    'invited_by_user_id' => $invitation->invited_by_user_id,
                    'status' => $invitation->status,
                    'group' => [
                        'id' => $invitation->group->id,
                        'name' => $invitation->group->name,
                        'speciality_needed' => $invitation->group->speciality_needed,
                        'image' => $this->imageService->getFullUrl($groupImagePath),
                    ],
                ];
            })
            ->toArray();
    }

    public function getGroupPendingInvitations(int $group, int $userId): array
    {
        if (!$this->groupMemberRepo->isMember($group, $userId) && !$this->groupMemberRepo->isLeader($group, $userId)) {
            throw new PermissionDeniedException(
                'لست عضو',
                'لا يمكنك رؤية الدعوات المرسلة انت لست عضو في هذه المجموعة ',
                403
            );
        }

        return $this->invitationRepo->getGroupInvitationsWithUser($group)
            ->map(function ($invitation) {
                $profileImagePath = optional($invitation->invitedUser->profile)->profile_image;

                return [
                    'id' => $invitation->id,
                    'group_id' => $invitation->group_id,
                    'user_id' => $invitation->invited_user_id,
                    'status' => $invitation->status->value,
                    'user' => [
                        'name' => $invitation->invitedUser->name,
                        'student_speciality' => optional($invitation->invitedUser->profile)->student_speciality,
                        'profile_image' => $this->imageService->getFullUrl($profileImagePath),
                    ],
                ];
            })
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

        $this->invitationRepo->updateStatus($invitation, GroupInvitationStatus::Cancelled);
    }
}

