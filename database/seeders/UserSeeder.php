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
                       'name' => 'Rawan Qaroune',
                       'email' => 'rawan@gmail.com' ,
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
