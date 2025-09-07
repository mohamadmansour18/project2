<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProjectForm;
use App\Models\ProjectForm2;

class ProjectForm2Seeder extends Seeder
{
    public function run(): void
    {

        $groupIds = ProjectForm::query()
            ->pluck('group_id')
            ->unique()
            ->values();

        if ($groupIds->isEmpty()) {
            return;
        }

        foreach ($groupIds as $gid) {
            if (ProjectForm2::query()->where('group_id', $gid)->exists()) {
                continue;
            }

            ProjectForm2::factory()
                ->pending()
                ->create([
                    'group_id' => $gid,
                ]);
        }
    }
}
