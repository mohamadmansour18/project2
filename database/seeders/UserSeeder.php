<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::transaction(function (){
            $users = [
                [
                   'user' => [
                       'name' => 'روان قرعوني',
                       'email' => 'obadawork912@gmail.com' ,
                       'password' => Hash::make('admin'),
                       'role' => UserRole::Admin->value ,
                       'email_verified_at' => now()
                   ],
                    'profile' => [
                        'governorate' => 'السويداء' ,
                        'profile_image' => null ,
                        'signature' => null
                    ]
                ],
                [
                    'user' => [
                        'name' => 'Carmen Al Shoufi' ,
                        'email' => 'carmenalshoufi8@gmail.com' ,
                        'role' => UserRole::Doctor->value
                    ],
                    'profile' => [

                    ]
                ],
                [
                    'user' => [
                        'name' => 'Obeda Al Rahal' ,
                        'university_number' => '1234' ,
                        'role' => UserRole::Student->value
                    ],
                    'profile' => [

                    ]
                ],
                [
                    'user' => [
                        'name' => 'Student One',
                        'email' => 'student1@gmail.com',
                        'password' => Hash::make('password'),
                        'role' => UserRole::Student->value,
                        'university_number' => '1001',
                        'email_verified_at' => now()
                    ],
                    'profile' => [

                    ]
                ],
                [
                    'user' => [
                        'name' => 'Student Two',
                        'email' => 'student2@gmail.com',
                        'password' => Hash::make('password'),
                        'role' => UserRole::Student->value,
                        'university_number' => '1002',
                        'email_verified_at' => now()
                    ],
                    'profile' => [

                    ]
                ],
            ];

            foreach ($users as $data)
            {
                $user = User::query()->create($data['user']);

                $profileData = array_merge($data['profile'] , [
                    'user_id' => $user->id ,
                ]);

                $profile = Profile::query()->create($profileData);
            }
        });

        User::factory()->count(500)->create();
    }
}
