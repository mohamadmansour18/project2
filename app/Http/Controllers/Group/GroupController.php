<?php

namespace App\Http\Controllers\Group;

use App\Enums\GroupMemberRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Models\Group;
use App\Services\GroupService;
use App\Traits\ApiSuccessTrait;

class GroupController extends Controller
{
    use ApiSuccessTrait ;

    protected GroupService $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

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

        // Only allow the leader to edit
        $isLeader = $group->members()
            ->where('user_id', $user->id)
            ->where('role', GroupMemberRole::Leader)
            ->exists();

        if (!$isLeader) {
            return response()->json(['message' => 'ليس لديك صلاحية التعديل على هذه المجموعة'], 403);
        }

        $this->groupService->updateGroup($request, $group);

        return $this->successResponse('تعديل مجموعة', 'تم تعديل بيانات المجموعة بنجاح', 200);
    }

    public function show(Group $group)
    {
        $data = $this->groupService->getGroupData($group);
        return $this->dataResponse($data ,200);
    }

}
