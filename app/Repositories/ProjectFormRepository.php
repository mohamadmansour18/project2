<?php

namespace App\Repositories;

use App\Enums\GroupMemberRole;
use App\Enums\ProjectFormStatus;
use App\Models\FormSignature;
use App\Models\ProjectForm;

class ProjectFormRepository
{
    public function countForm1GroupsForCurrentYear()
    {
        return ProjectForm::whereYear('created_at', now()->year)
            ->distinct('group_id')
            ->count('group_id');
    }

    public function getApprovedIdeasCountForCurrentYearDynamic($year): int
    {
        return ProjectForm::query()->whereYear('submission_date', $year)
            ->where('status' , ProjectFormStatus::Approved->value)
            ->count();
    }

    public function create(array $data): ProjectForm
    {
        return ProjectForm::create($data);
    }

    public function hasUserSigned(int $formId, int $userId): bool
    {
        return FormSignature::where('project_form_id', $formId)
            ->where('user_id', $userId)
            ->exists();
    }

    public function signForm(int $formId, int $userId): FormSignature
    {
        return FormSignature::create([
            'project_form_id' => $formId,
            'user_id' => $userId,
        ]);
    }

    public function update(ProjectForm $form, array $data): void
    {
        $form->update($data);
    }

    public function hasFormChanged(ProjectForm $form, array $data): bool
    {
        $isSupervisorChanged = $form->user_id !== (int) $data['user_id'];

        $contentFields = [
            'arabic_title',
            'english_title',
            'description',
            'project_scope',
            'targeted_sector',
            'sector_classification',
            'stakeholders',
        ];

        $hasContentChanged = collect($contentFields)->contains(
            fn($key) => $form->$key !== $data[$key]
        );

        return $isSupervisorChanged || $hasContentChanged;
    }

    public function markAsSubmitted(ProjectForm $form): void
    {
        $form->update([
            'status' => ProjectFormStatus::Pending,
            'submission_date' => now(),
        ]);
    }

    public function findById(int $id): ?ProjectForm
    {
        return ProjectForm::find($id);
    }

    public function existsForGroupByLeader(int $groupId, int $leaderId): bool
    {
        return ProjectForm::where('group_id', $groupId)
            ->whereHas('group.members', function ($query) use ($leaderId) {
                $query->where('user_id', $leaderId)
                    ->where('role', GroupMemberRole::Leader);
            })
            ->exists();
    }

    public function isApprovedForGroup(int $groupId): bool
    {
        return ProjectForm::where('group_id', $groupId)
            ->where('status', ProjectFormStatus::Approved)
            ->exists();
    }

}
