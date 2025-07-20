<?php

namespace App\Services;

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
    protected GroupRepository $groupRepo;
    protected ImageService $imageService;
    protected GroupMemberRepository $groupMemberRepo;
    protected GroupInvitationRepository $groupInvitationRepo;

    public function __construct(GroupRepository $groupRepo, ImageService $imageService, GroupMemberRepository $groupMemberRepo, GroupInvitationRepository $groupInvitationRepo) {
        $this->groupRepo = $groupRepo;
        $this->imageService = $imageService;
        $this->groupMemberRepo = $groupMemberRepo;
        $this->groupInvitationRepo = $groupInvitationRepo;
    }

    public function createGroup(CreateGroupRequest $request, User $creator): Group
    {
        //image
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $this->imageService->storeImageWithCustomName($request->file('image'), 'group_images', $request->name);
        }

        //QR
        $qrImagePathForDb = $this->imageService->generateAndStoreQrCode($request->name);

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
                $this->groupInvitationRepo->createInvitation($group->id, $inviteeId, $creator->id);
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
            $data['image'] = $this->imageService->storeImageWithCustomName($request->file('image'), 'group_images', $group->name);
        }

        //update
        $this->groupRepo->update($group, $data);

        return $group;
    }

    public function getGroupData(Group $group): array
    {
        return [
            'name' => $group->name,
            'description' => $group->description,
            'image' => $group->image ? $this->imageService->getFullUrl($group->image) : null,
            'speciality_needed' => $group->speciality_needed,
            'framework_needed' => $group->framework_needed,
            'type' => $group->type,
        ];
    }

}
