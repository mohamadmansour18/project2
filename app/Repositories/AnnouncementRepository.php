<?php

namespace App\Repositories;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementType;
use App\Models\Announcement;

class AnnouncementRepository
{
    public function getCurrentYearAnnouncements()
    {
        $currentYear = now()->year ;

        return Announcement::whereYear('created_at' , $currentYear)
            ->get();

    }

    public function create(array $data): Announcement
    {
        return Announcement::create($data);
    }

    public function find(int $id): ?Announcement
    {
        return Announcement::withTrashed()->find($id);
    }

    public function delete(Announcement $announcement): bool
    {
        return $announcement->delete(); // soft delete
    }

    public function getLatestAnnouncements(int $limit = 7)
    {
        return Announcement::orderBy('created_at', 'desc')
            ->take($limit)
            ->get();
    }

    public function getCurrentYearImagesByAudience(string $audience)
    {
        return Announcement::whereYear('created_at', now()->year)
            ->where('type', AnnouncementType::Image->value)
            ->where('audience', $audience)
            ->get();
    }

    public function getCurrentYearFilesByAudience(string $audience)
    {
        return Announcement::whereYear('created_at', now()->year)
            ->where('type', AnnouncementType::File->value)
            ->where('audience', $audience)
            ->get();
    }

    public function getLatestImages(int $limit = 5)
    {
        return Announcement::whereYear('created_at', now()->year)
            ->where('type', AnnouncementType::Image->value)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getLatestFiles(int $limit = 5)
    {
        return Announcement::whereYear('created_at', now()->year)
            ->where('type', AnnouncementType::File->value)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function getImagesForStudents()
    {
        return Announcement::whereYear('created_at', now()->year)
            ->where('audience', AnnouncementAudience::All->value)
            ->where('type', AnnouncementType::Image->value)
            ->get();
    }

    public function getFilesForStudents()
    {
        return Announcement::whereYear('created_at', now()->year)
            ->where('audience', AnnouncementAudience::All->value)
            ->where('type', AnnouncementType::File->value)
            ->get();
    }

    public function getLatestImagesForStudents()
    {
        return Announcement::whereYear('created_at', now()->year)
            ->where('audience', AnnouncementAudience::All->value)
            ->where('type', AnnouncementType::Image->value)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    public function getLatestFilesForStudents()
    {
        return Announcement::whereYear('created_at', now()->year)
            ->where('audience', AnnouncementAudience::All->value)
            ->where('type', AnnouncementType::File->value)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    public function getLastYearImages()
    {
        $lastYear = now()->subYear()->year;

        return Announcement::where('audience', AnnouncementAudience::All->value)
            ->where('type', AnnouncementType::Image->value)
            ->whereYear('created_at', $lastYear)
            ->get();
    }

    public function getLastYearFiles()
    {
        $lastYear = now()->subYear()->year;

        return Announcement::where('audience', AnnouncementAudience::All->value)
            ->where('type', AnnouncementType::File->value)
            ->whereYear('created_at', $lastYear)
            ->get();
    }

}
