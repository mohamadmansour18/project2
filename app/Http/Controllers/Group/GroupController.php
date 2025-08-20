<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangeLeadershipRequest;
use App\Http\Requests\CreateGroupRequest;
use App\Http\Requests\SearchGroupRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Models\Group;
use App\Services\GroupService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

class GroupController extends Controller
{
    use ApiSuccessTrait;

    public function __construct(protected GroupService $groupService)
    {
    }

    public function store(CreateGroupRequest $request): JsonResponse
    {
        $user = auth()->user();
        if ($user->groupMember()->exists()) {
            return response()->json(['message' => 'أنت بالفعل عضو في مجموعة'], 403);
        }

        $this->groupService->createGroup($request, $user);

        return $this->successResponse('انشاء مجموعة', 'تم إنشاء المجموعة بنجاح', 201);

    }

    public function update(UpdateGroupRequest $request, Group $group): JsonResponse
    {

        $this->groupService->updateGroup($request, $group);

        return $this->successResponse('تعديل مجموعة', 'تم تعديل بيانات المجموعة بنجاح', 200);
    }

    public function show(Group $group): JsonResponse
    {
        $data = $this->groupService->getGroupData($group);
        return $this->dataResponse($data, 200);
    }

    public function ChangeLeadership(ChangeLeadershipRequest $request, Group $group): JsonResponse
    {
        $this->groupService->changeLeadership($group, auth()->id(), $request->new_leader_id);
        return $this->successResponse('نقل القيادة', 'تم نقل القيادة بنجاح', 200);
    }

    public function getIncompletePublicGroups(): JsonResponse
    {
        $groups = $this->groupService->getIncompletePublicGroups();
        return $this->dataResponse(['groups' => $groups]);
    }

    public function myGroup(): JsonResponse
    {
        $userId = auth()->id();
        $data = $this->groupService->getMyGroup($userId);

        return $this->dataResponse(['data' => $data]);
    }

    public function showPublic($groupId): JsonResponse
    {
        $details = $this->groupService->getGroupDetails2($groupId);

        return $this->dataResponse(['details' => $details]);
    }

    public function myGroupDetails(): JsonResponse
    {
        $details = $this->groupService->getMyGroupDetails();

        return $this->dataResponse(['details' => $details]);
    }

    public function showAllGroup(): JsonResponse
    {
        $data = $this->groupService->getGroupDataForDoctor();

        return response()->json($data, 200);
    }

    public function showDoctorFormOneGroup(): JsonResponse
    {
        $data = $this->groupService->getGroupFormOneForDoctor();

        return response()->json($data, 200);
    }

    public function showGroupDetailsInInterview(int $groupId): JsonResponse
    {
        $data = $this->groupService->getGroupDetailsForFinalInterview($groupId);

        return $this->dataResponse($data, 200);
    }

    public function index(): JsonResponse
    {
        $groups = $this->groupService->getGroupsWithForms();

        return $this->dataResponse([GroupResource::collection($groups)->response()->getData(true)['data']], 200);
    }

    public function search(SearchGroupRequest  $request): JsonResponse
    {
        $keyword = $request->query('name', '');
        $groups = $this->groupService->searchGroupsByName($keyword);

        return $this->dataResponse([GroupResource::collection($groups)->response()->getData(true)['data']], 200);
    }

    public function showGroupDetailsFormOneRequest(int $groupId): JsonResponse
    {
        $data = $this->groupService->getGroupDetailsForFormOneRequest($groupId);

        return $this->dataResponse($data , 200);
    }
}
