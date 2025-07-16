<?php

namespace App\Repositories;

use App\Models\Announcement;

class AnnouncementRepository
{
    public function getCurrentYearAnnouncements()
    {
        $currentYear = now()->year ;

        return Announcement::whereYear('created_at' , $currentYear)
            ->get();

    }
}
