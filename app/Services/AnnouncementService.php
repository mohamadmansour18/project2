<?php

namespace App\Services;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementType;
use App\Exceptions\AnnouncementException;
use App\Exceptions\InvalidAttachmentException;
use App\Exceptions\NotFoundException;
use App\Exceptions\PermissionDeniedException;
use App\Models\Announcement;
use App\Models\User;
use App\Repositories\AnnouncementRepository;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AnnouncementService
{
    public function __construct(protected AnnouncementRepository $repo) {}

    public function createAnnouncement(array $data, User $user): void
    {
        $file = request()->file('attachment_path');
        $type = $data['type'];

        // تحقق من نوع الملف
        if ($type === 'image' && !str_starts_with($file->getMimeType(), 'image/')) {
            throw new InvalidAttachmentException(body: 'يجب رفع صورة عند اختيار النوع صورة.');
        }

        if ($type === 'file' && str_starts_with($file->getMimeType(), 'image/')) {
            throw new InvalidAttachmentException(body: 'يجب رفع ملف غير صورة عند اختيار النوع ملف.');
        }

        $originalTitle = $data['title']; // هذا اللي دخله المستخدم
        $timestamp = now()->format('Ymd_His');

        $extension = $file->getClientOriginalExtension();
        $filename = $originalTitle . '_' . $timestamp . '.' . $extension;

        // خزّن الملف باسم جديد على disk public
        $path = $file->storeAs('announcements', $filename, 'public');

        $data['attachment_path'] = $path;
        $data['type'] = AnnouncementType::from($data['type']);
        $data['audience'] = AnnouncementAudience::from($data['audience']);

        $this->repo->create($data);
    }

    public function deleteAnnouncement(Announcement $announcement): bool {

        return $this->repo->delete($announcement);
    }

    public function downloadAttachment(Announcement $announcement)
    {
        if (!Storage::disk('public')->exists($announcement->attachment_path)) {
            throw new NotFoundException('الملف غير موجود', 'المرفق المطلوب غير موجود في السيرفر');
        }

        return Storage::disk('public')->download($announcement->attachment_path);
    }

    public function previewAttachment(Announcement $announcement)
    {
        if (!Storage::disk('public')->exists($announcement->attachment_path)) {
            throw new NotFoundException('الملف غير موجود', 'المرفق المطلوب غير موجود في السيرفر');
        }

        if ($announcement->type === AnnouncementType::Image) {
            return response()->file(Storage::disk('public')->path($announcement->attachment_path));
        }

        throw new PermissionDeniedException(
            'لا يمكن معاينة الملف',
            'هذا النوع من الملفات لا يدعم المعاينة، الرجاء التحميل بدلاً من ذلك'
        );
    }

    public function getCurrentYearImages()
    {
        return $this->repo->getCurrentYearAnnouncements()
            ->where('type', AnnouncementType::Image);
    }

    public function getCurrentYearFiles()
    {
        return $this->repo->getCurrentYearAnnouncements()
            ->where('type', AnnouncementType::File);
    }

    public function getLatestAnnouncements(int $limit = 7)
    {
        return $this->repo->getLatestAnnouncements($limit);
    }

    public function getAdminImages() {
        return $this->repo->getCurrentYearImagesByAudience('professors');
    }

    public function getAdminFiles() {
        return $this->repo->getCurrentYearFilesByAudience('professors');
    }

    public function getLatestImages($limit = 5) {
        return $this->repo->getLatestImages($limit);
    }

    public function getLatestFiles($limit = 5) {
        return $this->repo->getLatestFiles($limit);
    }

    public function getImagesForStudents()
    {
        return $this->repo->getImagesForStudents();
    }

    public function getFilesForStudents()
    {
        return $this->repo->getFilesForStudents();
    }

    public function getLatestImagesForStudents()
    {
        return $this->repo->getLatestImagesForStudents();
    }

    public function getLatestFilesForStudents()
    {
        return $this->repo->getLatestFilesForStudents();
    }

    public function getLastYearImages()
    {
        return $this->repo->getLastYearImages();
    }

    public function getLastYearFiles()
    {
        return $this->repo->getLastYearFiles();
    }

    public function getAllAnnouncements(string $type , ?string $audience = null): array
    {
        $announcements = $this->repo->getByTypeForCurrentYear($type , $audience)->map(function ($announcement){
            return [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'type' => $announcement->type,
                'created_at' => $announcement->created_at->format('Y-m-d'),
            ];
        });

        $latest = $announcements->take(4);
        $old = $announcements->skip(4)->values();
        $oldCount = $old->count();

        return [
            'latest_announcements' => $latest,
            'old_announcements' => $old,
            'count' => $oldCount,
        ];
    }

    public function downloadAnnouncement(int $annoId): BinaryFileResponse
    {
        $announcement = $this->repo->findAnnouncement($annoId);

        if(!$announcement)
        {
            throw new AnnouncementException('لايمكنك اجراء هذه العملية !', 'الإعلان الذي تحاول الوصول اليه غير موجود', 404);
        }

        $filePath = $this->repo->getAnnouncementPath($announcement);

        if(!$filePath)
        {
            throw new AnnouncementException('لايمكنك اجراء هذه العملية !', 'ملف الإعلان الذي تحاول تنزيله غير موجود أساساً', 404);
        }

        return response()->download($filePath , basename($filePath));
    }
}
