<?php

namespace Database\Factories;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementType;
use App\Models\Announcement;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Faker\Generator as FakerGenerator;


class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    // Faker عربي
    protected function withFaker(): FakerGenerator
    {
        return \Faker\Factory::create('ar_SA');
    }

    public function definition(): array
    {
        $typeValue = $this->faker->randomElement(['image', 'file']);

        $ext = $typeValue === 'image'
            ? $this->faker->randomElement(['jpg', 'jpeg', 'png'])
            : $this->faker->randomElement(['pdf', 'docx']);

        $audValue = $this->faker->randomElement(['all', 'professors']);

        $typeField = enum_exists(AnnouncementType::class)
            ? AnnouncementType::from($typeValue)
            : $typeValue;

        $audienceField = enum_exists(AnnouncementAudience::class)
            ? AnnouncementAudience::from($audValue)
            : $audValue;

        return [
            'title'           => $this->faker->sentence(3),
            'type'            => $typeField,
            'attachment_path' => '/uploads/announcements/' . Str::uuid() . '.' . $ext,
            'audience'        => $audienceField, // 'all' أو 'professors'
        ];
    }

    // حالات اختيارية إن رغبت
    public function image(): static
    {
        return $this->state(function () {
            return ['type' => enum_exists(AnnouncementType::class) ? AnnouncementType::from('image') : 'image'];
        });
    }

    public function file(): static
    {
        return $this->state(function () {
            return ['type' => enum_exists(AnnouncementType::class) ? AnnouncementType::from('file') : 'file'];
        });
    }

    public function forAll(): static
    {
        return $this->state(function () {
            return ['audience' => enum_exists(AnnouncementAudience::class) ? AnnouncementAudience::from('all') : 'all'];
        });
    }

    public function forProfessors(): static
    {
        return $this->state(function () {
            return ['audience' => enum_exists(AnnouncementAudience::class) ? AnnouncementAudience::from('professors') : 'professors'];
        });
    }
}

