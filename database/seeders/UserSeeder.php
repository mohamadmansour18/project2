<?php

namespace Database\Seeders;

use App\Enums\ProfileStudentStatus;
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
                        'profile_image' => 'doctor_profile_image/rawan.jpg' ,
                        'signature' => null
                    ]
                ],
                [
                    'user' => [
                        'name' => 'الدكتور تيست',
                        'email' => '360mohamad360@gmail.com' ,
                        'password' => Hash::make('admin123'),
                        'role' => UserRole::Doctor->value ,
                        'email_verified_at' => now()
                    ],
                    'profile' => [
                        'governorate' => 'دمشق' ,
                        'profile_image' => null
                    ]
                ]
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


    }
}
