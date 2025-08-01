<?php

namespace App\Services;

use App\Repositories\GroupMemberRepository;


class GroupMemberService
{
    public function __construct(
        protected GroupMemberRepository $groupMemberRepo,
        protected ImageService $imageService,
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
                'profile_image' => $this->imageService->getFullUrl($profileImage),
            ];
        })->toArray();
    }
}
