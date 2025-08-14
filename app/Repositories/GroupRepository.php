<?php

namespace App\Repositories;

use App\Enums\GroupMemberRole;
use App\Enums\GroupType;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\GroupMember;
use App\Enums\GroupInvitationStatus;
use Illuminate\Support\Collection;

class GroupRepository
{
    public function getGroupsCountForCurrentYear(): int
    {
        return Group::query()->whereYear('created_at', now()->year)->count();
    }

    public function getGroupsCountForCurrentYearDynamic($year): int
    {
        return Group::query()->whereYear('created_at', $year)
            ->where('number_of_members' , '>=' , 4)
            ->count();
    }

    public function create(array $data): Group
    {
        return Group::create($data);
    }

    public function update(Group $group, array $data): Group
    {
        $group->update($data);
        return $group;
    }

    public function getGroupDetails(Group $group): array
    {
        return [
            'name' => $group->name,
            'description' => $group->description,
            'image' => $group->image,
            'speciality_needed' => $group->speciality_needed,
            'framework_needed' => $group->framework_needed,
            'type' => $group->type,
        ];
    }

    public function getById(int $id): ?Group
    {
        return Group::find($id);
    }

    public function getMemberCount(int $groupId): int
    {
        $group = $this->getById($groupId);
        return $group ? $group->number_of_members : 0;
    }

    public function getIncompletePublicGroupsForCurrentYear(): Collection
    {
        return Group::query()
            ->where('type', GroupType::Public->value)
            ->whereYear('created_at', now()->year)
            ->where('number_of_members', '<', 5)
            ->get(['id', 'name', 'image', 'speciality_needed', 'number_of_members']);
    }

    public function getUserGroup(int $userId)
    {
        return Group::query()
            ->whereHas('members', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->first();
    }




}
