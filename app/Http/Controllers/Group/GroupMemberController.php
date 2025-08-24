<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Resources\GroupMemberResource;
use App\Services\GroupMemberService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

class GroupMemberController extends Controller
{
    use ApiSuccessTrait ;

    public function __construct(protected GroupMemberService $groupMemberService)
    {}

    public function myGroupMembers()
    {
        $userId = auth()->id();

        $members = $this->groupMemberService->getMyGroupMembers($userId);

        return $this->dataResponse(['members' => $members]);
    }

    public function myGroupMembersFormOne(): JsonResponse
    {
        $members = $this->groupMemberService->getMyGroupMembersFormOne();

        return response()->json([
            'count' => $members->count(),
            'data'  => GroupMemberResource::collection($members),
        ]);
    }

}
