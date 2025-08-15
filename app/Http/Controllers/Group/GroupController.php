<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeLeadershipRequest;
use App\Http\Requests\CreateGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Group;
use App\Services\GroupService;
use App\Traits\ApiSuccessTrait;

class GroupController extends Controller
{
    use ApiSuccessTrait ;

    public function __construct(protected GroupService $groupService)
    {}

    public function store(CreateGroupRequest $request)
    {
        $user = auth()->user();
        if ($user->groupMember()->exists()) {
            return response()->json(['message' => 'أنت بالفعل عضو في مجموعة'], 403);
        }

        $this->groupService->createGroup($request, $user);

        return $this->successResponse('انشاء مجموعة' , 'تم إنشاء المجموعة بنجاح' , 201);

    }

    public function update(UpdateGroupRequest $request, Group $group)
    {
        $user = auth()->user();

        $this->groupService->updateGroup($request, $group);

        return $this->successResponse('تعديل مجموعة', 'تم تعديل بيانات المجموعة بنجاح', 200);
    }

    public function show(Group $group)
    {
        $data = $this->groupService->getGroupData($group);
        return $this->dataResponse($data ,200);
    }

    public function ChangeLeadership(ChangeLeadershipRequest $request, Group $group)
    {
        $this->groupService->changeLeadership($group, auth()->id(), $request->new_leader_id);
        return $this->successResponse('نقل القيادة', 'تم نقل القيادة بنجاح', 200);
    }

    public function getIncompletePublicGroups()
    {
        $groups = $this->groupService->getIncompletePublicGroups();
        return $this->dataResponse(['groups' => $groups]);
    }

    public function myGroup()
    {
        $userId = auth()->id();
        $data = $this->groupService->getMyGroup($userId);

        return $this->dataResponse(['data' => $data]);
    }

    public function showPublic($groupId)
    {
        $details = $this->groupService->getGroupDetails2($groupId);

        return $this->dataResponse(['details' => $details]);
    }

    public function myGroupDetails()
    {
        $details = $this->groupService->getMyGroupDetails();

        return $this->dataResponse(['details' => $details]);
    }

}
