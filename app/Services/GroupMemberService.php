<?php

namespace App\Services;

use App\Enums\GroupMemberRole;
use App\Helpers\UrlHelper;
use App\Repositories\GroupMemberRepository;


class GroupMemberService
{
    public function __construct(
        protected GroupMemberRepository $groupMemberRepo,
    ){}

    public function getMyGroupMembers(int $userId): array
    {
        $members = $this->groupMemberRepo->getMembersForUserGroup($userId);

        return $members->map(function ($member) {
            $profileImage = optional($member->user->profile)->profile_image;

            return [
                'id' => $member->id,
                'user_id'=> $member->user->id,
                'name' => $member->user->name,
                'profile_image' => UrlHelper::imageUrl($profileImage),
                'is_leader' => $member->role === GroupMemberRole::Leader,
            ];
        })->toArray();
    }
}
