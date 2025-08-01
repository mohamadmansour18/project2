<?php

namespace App\Repositories;

use App\Enums\ProjectFormStatus;
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
}
