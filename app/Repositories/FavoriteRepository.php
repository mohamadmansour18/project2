<?php

namespace App\Repositories;

use App\Enums\AnnouncementType;
use App\Models\Announcement;
use Illuminate\Database\Eloquent\Collection;

class FavoriteRepository
{

    public function getUserImageFavorites($user): Collection
    {
        return $user->Announcements()
            ->where('type', AnnouncementType::Image)
            ->get();
    }

    public function getUserFileFavorites($user): Collection
    {
        return $user->Announcements()
            ->where('type', AnnouncementType::File)
            ->get();
    }

    public function addFavorite($user, Announcement $announcement)
    {
        return $user->Announcements()->syncWithoutDetaching([$announcement->id]);
    }

    public function removeFavorite($user, Announcement $announcement)
    {
        return $user->Announcements()->detach($announcement->id);
    }
}
