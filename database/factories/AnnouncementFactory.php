<?php

namespace Database\Factories;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementType;
use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class ;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'type' => $this->faker->randomElement(AnnouncementType::convertEnumToArray()),
            'attachment_path' => $this->faker->url ,
            'audience' => $this->faker->randomElement(AnnouncementAudience::convertEnumToArray())
        ];
    }
}
