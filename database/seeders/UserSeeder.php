<?php

namespace Database\Seeders;

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
            $admin = User::query()->create([
                'name' => 'Rawan Qaroune' ,
                'email' => 'rawan@gmail.com' ,
                'password' => Hash::make('admin'),
                'role' => 'admin'
            ]);

            $adminProfile = Profile::query()->create([
                'user_id' => User::query()->where('email' . 'rawan@gmail.com')->pluck('id'),
                'governorate' => 'السويداء',
                'profile_image' => 'path' ,
                'signature' => 'path'
            ]);
        });

    }
}
