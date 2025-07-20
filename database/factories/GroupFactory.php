<?php

namespace Database\Factories;

use App\Enums\GroupType;
use App\Models\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    protected $model = Group::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company . ' Group' ,
            'description' => $this->faker->paragraph,
            'speciality_needed' => $this->faker->randomElements([
                'backend' , 'frontWeb' , 'frontMobile' , 'UI/UX'
            ] , rand(1,3)),
            'framework_needed' => $this->faker->randomElements([
                'Laravel', 'React', 'Flutter', 'Node.js', 'Vue'
            ] , rand(1,3)),
            'type' => $this->faker->randomElement(GroupType::convertEnumToArray()),
            'qr_code' => $this->faker->uuid ,
            'number_of_members' => $this->faker->numberBetween(1,6),
            'image' => $this->faker->url,
        ];
    }
}
