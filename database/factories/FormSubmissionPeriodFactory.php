<?php

namespace Database\Factories;

use App\Enums\FormSubmissionPeriodFormName;
use App\Models\FormSubmissionPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FormSubmissionPeriod>
 */
class FormSubmissionPeriodFactory extends Factory
{
    protected $model = FormSubmissionPeriod::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'form_name' => $this->faker->randomElement(FormSubmissionPeriodFormName::convertEnumToArray()),
            'start_date' => now(),
            'end_date' => now()->addDays($this->faker->numberBetween(1, 80)),
        ];
    }
}
