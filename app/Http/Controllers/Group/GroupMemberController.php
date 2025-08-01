<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Services\GroupMemberService;
use App\Traits\ApiSuccessTrait;

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

}
