<?php

namespace Database\Factories;

use App\Models\ProjectForm;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Faker\Generator as FakerGenerator;

class ProjectFormFactory extends Factory
{
    protected $model = ProjectForm::class;

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
            'user_id'                => 2,
            'arabic_title'           => $this->faker->catchPhrase(),
            'english_title'          => $fakerEn->catchPhrase(),
            'description'            => $this->faker->realText(220),
            'project_scope'          => $this->faker->sentence(10),
            'targeted_sector'        => $this->faker->sentence(8),
            'sector_classification'  => $this->faker->sentence(8),
            'stakeholders'           => $this->faker->sentence(8),
            'supervisor_signature'   => null,
            'filled_form_file_path'  => '/fake/forms/' . Str::uuid() . '.pdf',
            'submission_date'        => null,
            'status'                 => 'pending',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => 'pending']);
    }
}
