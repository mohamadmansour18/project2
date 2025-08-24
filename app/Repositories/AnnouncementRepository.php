<?php

namespace App\Repositories;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementType;
use App\Models\Announcement;
use Illuminate\Support\Facades\Storage;

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
        return Announcement::with('users') // 👈 eager load favorites
        ->whereYear('created_at', now()->year)
            ->where('audience', AnnouncementAudience::All->value)
            ->where('type', AnnouncementType::Image->value)
            ->get();
    }

    public function getFilesForStudents()
    {
        return Announcement::with('users')
            ->whereYear('created_at', now()->year)
            ->where('audience', AnnouncementAudience::All->value)
            ->where('type', AnnouncementType::File->value)
            ->get();
    }

    public function getLatestImagesForStudents()
    {
        return Announcement::with('users')
            ->whereYear('created_at', now()->year)
            ->where('audience', AnnouncementAudience::All->value)
            ->where('type', AnnouncementType::Image->value)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    public function getLatestFilesForStudents()
    {
        return Announcement::with('users')
            ->whereYear('created_at', now()->year)
            ->where('audience', AnnouncementAudience::All->value)
            ->where('type', AnnouncementType::File->value)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    public function getLastYearImages()
    {
        $lastYear = now()->subYear()->year;

        return Announcement::with('users')
            ->where('audience', AnnouncementAudience::All->value)
            ->where('type', AnnouncementType::Image->value)
            ->whereYear('created_at', $lastYear)
            ->get();
    }

    public function getLastYearFiles()
    {
        $lastYear = now()->subYear()->year;

        return Announcement::with('users')
            ->where('audience', AnnouncementAudience::All->value)
            ->where('type', AnnouncementType::File->value)
            ->whereYear('created_at', $lastYear)
            ->get();
    }

    public function getByTypeForCurrentYear(string $type , ?string $audience = null)
    {
        $query = Announcement::select('id', 'title', 'type' , 'audience' , 'created_at')
            ->where('type' , $type)
            ->whereYear('created_at', now()->year);

        if($audience)
        {
            $query->where('audience', $audience);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function findAnnouncement(int $annoId)
    {
        return Announcement::find($annoId);
    }
    public function getAnnouncementPath(Announcement $Announcement): ?string
    {

        if(!$Announcement->attachment_path)
        {
            return null;
        }
        if(!Storage::disk('public')->exists($Announcement->attachment_path))
        {
            return null;
        }

        return Storage::disk('public')->path($Announcement->attachment_path);
    }
}
