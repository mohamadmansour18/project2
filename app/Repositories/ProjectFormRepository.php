<?php

namespace App\Repositories;

use App\Enums\GroupMemberRole;
use App\Enums\ProjectFormStatus;
use App\Models\FormSignature;
use App\Models\GroupMember;
use App\Models\ProjectForm;
use Illuminate\Support\Facades\Storage;

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

    public function approve(ProjectForm $form): ProjectForm
    {
        $form->status = ProjectFormStatus::Approved;
        $form->submission_date = now();
        $form->save();

        return $form;
    }

    public function reject(ProjectForm $form): ProjectForm
    {
        $form->status = ProjectFormStatus::Rejected;
        $form->save();

        return $form;
    }

    public function getLeaderGroupFromForm(int $groupId)
    {
        return GroupMember::with('user')->where('group_id', $groupId)
            ->where('role' , GroupMemberRole::Leader->value)
            ->first();
    }

    public function getFilePath(ProjectForm $form): ?string
    {
        if(!$form->filled_form_file_path)
        {
            return null;
        }
        if(!Storage::disk('public')->exists($form->filled_form_file_path))
        {
            return null;
        }

        return Storage::disk('public')->path($form->filled_form_file_path);
    }

    public function getFormByGroupId(int $groupId)
    {
        return ProjectForm::query()->where('group_id' , $groupId)->first();
    }
}
