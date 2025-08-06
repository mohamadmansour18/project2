<?php

namespace Database\Factories;

use App\Enums\ProfileGovernorate;
use App\Enums\ProfileStudentStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;
    public function definition(): array
    {
        $role = $this->faker->randomElement([UserRole::Student->value , UserRole::Doctor->value]);

        return [
            'name' => $this->faker->name ,
            'university_number' => $role === UserRole::Student->value ? $this->faker->unique()->numerify('2025###') : null ,
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => $role
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            if($user->role === UserRole::Student)
            {
                $user->profile()->create([
                    'governorate' => null,
                    'student_status' => ProfileStudentStatus::Fourth_Year->value
                ]);
            }
            elseif ($user->role === UserRole::Doctor)
            {
                $user->profile()->create([
                    'governorate' => fake()->randomElement(ProfileGovernorate::convertEnumToArray())
                ]);
            }
        });
    }
}
