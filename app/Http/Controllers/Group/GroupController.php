<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateGroupRequest;
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

}
