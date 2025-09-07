<?php

namespace Database\Factories;

use App\Models\ProjectForm2;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Faker\Generator as FakerGenerator;

class ProjectForm2Factory extends Factory
{
    protected $model = ProjectForm2::class;

    /**
     * Faker عربي افتراضي
     */
    protected function withFaker(): FakerGenerator
    {
        return \Faker\Factory::create('ar_SA');
    }

    public function definition(): array
    {

        $fakerEn = \Faker\Factory::create('en_US');

        return [
            'group_id'               => Group::factory(),

            'arabic_project_title'   => $this->faker->sentence(4),
            'user_segment'           => $this->faker->sentence(8),
            'development_procedure'  => $this->faker->sentence(12),

            // جملة إنجليزية
            'libraries_and_tools'    => $fakerEn->sentence(8),

            // روابط وهمية
            'roadmap_file'           => 'https://example.com/roadmaps/' . Str::uuid() . '.pdf',
            'work_plan_file'         => 'https://example.com/workplans/' . Str::uuid() . '.pdf',
            'filled_form_file_path'  => 'https://example.com/forms/' . Str::uuid() . '.pdf',

            'status'                 => 'pending',
            'submission_date'     => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }
}
