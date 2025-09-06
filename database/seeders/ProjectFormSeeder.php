<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Group;
use App\Models\ProjectForm;

class ProjectFormSeeder extends Seeder
{
    public function run(): void
    {
        $groups = Group::query()->inRandomOrder()->take(10)->get();
        foreach ($groups as $group) {
            ProjectForm::factory()
                ->for($group)
                ->pending()
                ->create([
                    'user_id' => 2,
                ]);
        }
    }
}
