<?php

namespace App\Repositories;

use App\Models\ProjectForm;

class ProjectFormRepository
{
    public function countForm1GroupsForCurrentYear()
    {
        return ProjectForm::whereYear('created_at', now()->year)
            ->distinct('group_id')
            ->count('group_id');
    }
}
