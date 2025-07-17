<?php

namespace App\Repositories;

use App\Models\ProjectForm2;

class ProjectForm2Repository
{
    public function countForm2GroupsForCurrentYear()
    {
        return ProjectForm2::whereYear('created_at' , now()->year)
            ->distinct('group_id')
            ->count('group_id');
    }
}
