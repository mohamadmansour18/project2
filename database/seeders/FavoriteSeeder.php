<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Favorite;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(User::query()->exists() && Announcement::query()->exists())
        {
            Favorite::factory()->count(30)->create();
        } else {
            $this->command->warn("  Users or Announcements are missing . Skipping seeding AnnouncementViews");
        }
    }
}
