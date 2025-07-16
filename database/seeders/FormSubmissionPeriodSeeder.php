<?php

namespace Database\Seeders;

use App\Enums\FormSubmissionPeriodFormName;
use App\Models\FormSubmissionPeriod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FormSubmissionPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FormSubmissionPeriod::query()->delete();

        FormSubmissionPeriod::query()->create([
            'form_name' => 'form1',
            'start_date' => now(),
            'end_date' => now()->addDays(10),
        ]);

        FormSubmissionPeriod::query()->create([
            'form_name' => 'form2',
            'start_date' => now()->addDays(1),
            'end_date' => now()->addDays(15),
        ]);

        FormSubmissionPeriod::query()->create([
            'form_name' => 'interviews',
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(20),
        ]);

        //FormSubmissionPeriod::factory()->count(10)->create();
    }
}
