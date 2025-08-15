<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Exceptions\PermissionDeniedException;
use App\Helpers\ImageHelper;
use App\Helpers\UrlHelper;
use App\Http\Requests\CreateGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Group;
use App\Models\User;
use App\Repositories\GroupInvitationRepository;
use App\Repositories\GroupMemberRepository;
use App\Repositories\GroupRepository;
use App\Enums\GroupMemberRole;

class GroupService
{


    public function __construct(
      protected GroupRepository $groupRepo,
      protected GroupMemberRepository $groupMemberRepo,
      protected GroupInvitationRepository $groupInvitationRepo) {
    }

    public function createGroup(CreateGroupRequest $request, User $creator): Group
    {
        //image
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = ImageHelper::storeImageWithCustomName($request->file('image'), 'group_images', $request->name);
        }

        //QR
        $qrImagePathForDb = ImageHelper::generateAndStoreQrCode($request->name);

        //Create group
        $group = $this->groupRepo->create([
            'name' => $request->name,
            'description' => $request->description,
            'speciality_needed' => $request->speciality_needed,
            'framework_needed' => $request->framework_needed,
            'type' => $request->type,
            'qr_code' => $qrImagePathForDb,
            'number_of_members' => 1,
            'image' => $imagePath,
        ]);

        //add Leader
        $this->groupMemberRepo->create($group->id, $creator->id, GroupMemberRole::Leader);


        // send invitations
        if ($request->has('invitations')) {
            foreach ($request->invitations as $inviteeId) {
                $this->groupInvitationRepo->create($group->id, $inviteeId, $creator->id);
            }
        }


        return $group;
    }

    public function updateGroup(UpdateGroupRequest $request, Group $group): Group
    {
        //data
        $data = [];
        foreach ($request->all() as $key => $value) {
            if (in_array($key, ['name', 'description', 'speciality_needed', 'framework_needed', 'type'])) {
                $data[$key] = $value;
            }
        }

        //image
        if ($request->hasFile('image')) {
            $data['image'] = ImageHelper::storeImageWithCustomName($request->file('image'), 'group_images', $group->name);
        }

        //update
        $this->groupRepo->update($group, $data);

        return $group;
    }

    public function getGroupData(Group $group): array
    {
        $data = $this->groupRepo->getGroupDetails($group);

        $data['image'] = UrlHelper::imageUrl($data['image']);

        return $data;
    }

    public function changeLeadership(Group $group, int $currentLeaderId, int $newLeaderId): void
    {
        if (!$this->groupMemberRepo->isMember($group->id, $newLeaderId)) {
            throw new PermissionDeniedException(
                'عضو غير موجود',
                'المستخدم المحدد ليس عضوًا في المجموعة',
                400
            );
        }

        if ($currentLeaderId === $newLeaderId) {
            throw new PermissionDeniedException(
                'خطأ في النقل',
                'لا يمكنك نقل القيادة لنفسك',
                400
            );
        }

        $this->groupMemberRepo->updateRole($group->id, $newLeaderId, GroupMemberRole::Leader);
        $this->groupMemberRepo->updateRole($group->id, $currentLeaderId, GroupMemberRole::Member);
    }

    public function getIncompletePublicGroups(): array
    {
        $groups = $this->groupRepo->getIncompletePublicGroupsForCurrentYear();

        return $groups->map(function ($group) {
            return [
                'id' => $group->id,
                'name' => $group->name,
                'image' => UrlHelper::imageUrl($group->image),
                'specialities_needed' => $group->speciality_needed,
                'members_count' => $group->number_of_members,
            ];
        })->toArray();
    }

    public function getMyGroup(int $userId): ?array
    {
        $group = $this->groupRepo->getUserGroup($userId);

        if (!$group) {
            throw new PermissionDeniedException(
                'خطأ',
                'أنت لست عضو في أي مجموعة',
                404
            );
        }

        return $this->getGroupData($group);
    }

    public function getGroupDetails2(int $groupId): array
    {
        $group = $this->groupRepo->getGroupWithRelations($groupId);

        $form1 = $group->projectForms()->first();

        $supervisorSignature = $form1?->signatures()
            ->whereHas('user', function ($q) {
                $q->where('role', UserRole::Doctor);
            })
            ->with('user')
            ->first();

        return [
            'supervisor_name' => $supervisorSignature?->user?->name,
            'group_created_at' => $group->created_at->toDateString(),
            'idea_arabic_name' => $form1?->arabic_title,
            'members_count' => $group->members->count(),
            'members' => $group->members->map(function ($member) {
                return [
                    'name' => $member->user->name,
                    'speciality' => $member->user->profile?->student_speciality,
                    'student_status' => $member->user->profile?->student_status,
                    'image' => UrlHelper::imageUrl( $member->user->profile?->profile_image),
                    'is_leader' => $member->role === GroupMemberRole::Leader,
                ];
            }),
            'qr_code' => UrlHelper::imageUrl($group->qr_code),
        ];
        }

    public function getMyGroupDetails(): ?array
    {
        $user = auth()->user();
        $group = $this->groupRepo->getUserGroupWithRelations($user->id);

        if (!$group) {
            throw new PermissionDeniedException(
                'خطأ',
                'أنت لست عضو في أي مجموعة',
                404
            );
        }

        $form1 = $group->projectForms()->first();

        $supervisorSignature = $form1?->signatures()
            ->whereHas('user', function ($q) {
                $q->where('role', UserRole::Doctor);
            })
            ->with('user')
            ->first();

        return [
            'group_id' => $group->id,
            'supervisor_name' => $supervisorSignature?->user?->name,
            'group_created_at' => $group->created_at->toDateString(),
            'idea_arabic_name' => $form1?->arabic_title,
            'members_count' => $group->members->count(),
            'members' => $group->members->map(function ($member) {
                return [
                    'name' => $member->user->name,
                    'speciality' => $member->user->profile?->student_speciality,
                    'student_status' => $member->user->profile?->student_status,
                    'image' => UrlHelper::imageUrl( $member->user->profile?->profile_image),
                    'is_leader' => $member->role === GroupMemberRole::Leader,
                ];
            }),
            'qr_code' => UrlHelper::imageUrl($group->qr_code),
        ];
    }


}
