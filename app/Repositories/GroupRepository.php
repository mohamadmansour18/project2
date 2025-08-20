<?php

namespace App\Repositories;

use App\Enums\GroupMemberRole;
use App\Enums\GroupType;
use App\Enums\ProjectFormStatus;
use App\Models\GradeException;
use App\Models\Group;
use App\Models\GroupInvitation;
use App\Models\GroupMember;
use App\Enums\GroupInvitationStatus;
use App\Models\InterviewCommittee;
use App\Models\InterviewSchedule;
use App\Models\ProjectGrade;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

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

    public function getGroupWithRelations(int $groupId)
    {
        return Group::with([
            'members.user.profile',
            'projectForms.signatures.user.profile'
        ])->findOrFail($groupId);
    }

    public function getUserGroupWithRelations(int $userId)
    {
        return Group::with([
            'members.user.profile',
            'projectForms.signatures.user'
        ])
            ->whereHas('members', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->first();
    }

    public function getAllGroupsWithForms(): Collection|array
    {
        return Group::with(['projectForms:id,group_id,status' , 'projectForm2:id,group_id'])
            ->whereYear('created_at' , now()->year)
            ->get(['id' , 'name' , 'image' , 'number_of_members']);
    }

    public function getDoctorFormOneGroups(int $doctorId): Collection|array
    {
        return Group::query()
            ->whereHas('projectForms' , function($query) use ($doctorId) {
                $query->where('user_id' , $doctorId);
            })
            ->with(['projectForms:id,group_id,status' , 'projectForm2:id,group_id'])
            ->whereYear('created_at' , now()->year)
            ->get(['id' , 'name' , 'image' , 'number_of_members']);
    }

    public function getGroupDetailsInFinalInterview(int $groupId): array
    {
        $group = Group::with(['members.user.profile' , 'projectForms' , 'projectForm2'])->findOrFail($groupId);

        $schedule = InterviewSchedule::query()->where('group_id' , $groupId)->first();

        $form1 = $group->projectForms()->where('status' , ProjectFormStatus::Approved->value)->first();

        $form2 = $group->projectForm2()->first();

        $grade = ProjectGrade::query()->where('group_id' , $groupId)->first();

        $exceptionGrades = [];
        if($grade)
        {
            $exceptionGrades = GradeException::query()
                ->where('grade_id' , $grade->id)
                ->with('user:id,name')
                ->get();
        }

        $isSupervisor = InterviewCommittee::query()->where('supervisor_id' , Auth::id())->exists();

        return compact('group' , 'schedule' , 'form1' , 'form2' , 'grade' , 'exceptionGrades' , 'isSupervisor');
    }

    public function getGroupWithRelation($groupId): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
    {
        return Group::with([
            'members.user.profile',
            'projectForms' => function($query){
                $query->whereIn('status' , [ProjectFormStatus::Approved->value , ProjectFormStatus::Pending->value]);
            },
            'interviewSchedules.committee',
            'projectGrade'
        ])->findOrFail($groupId);
    }


}
