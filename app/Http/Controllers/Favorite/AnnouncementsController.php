<?php

namespace App\Http\Controllers\Favorite;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAnnouncementRequest;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\DoctorAnnouncementResource;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use App\Services\HomeMobileService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AnnouncementsController extends Controller
{
    use ApiSuccessTrait ;

    public function __construct(
        protected HomeMobileService $homeService ,
        protected AnnouncementService $service
    ) { }

    public function getAnnouncementStatistics(): JsonResponse
    {
        $response = $this->homeService->getAnnouncementStatistics();

        return $this->dataResponse($response , 200);
    }
//
//    public function getStudentAnnouncementStatistics(): JsonResponse
//    {
//        $response = $this->homeService->getStudentAnnouncementStatistics();
//
//        return $this->dataResponse($response, 200);
//    }

    public function store(StoreAnnouncementRequest $request): JsonResponse
    {
        $this->service->createAnnouncement($request->validated(), auth()->user());

        return $this->successResponse('إنشاء إعلان', 'تم إنشاء الإعلان بنجاح', 201);
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        $this->service->deleteAnnouncement($announcement);

        return $this->successResponse('حذف إعلان', 'تم حذف الإعلان بنجاح', 200);
    }

    public function download(Announcement $announcement)
    {
        return $this->service->downloadAttachment($announcement);
    }

    public function preview(Announcement $announcement)
    {
        return $this->service->previewAttachment($announcement);
    }

    public function getCurrentYearImages(): JsonResponse
    {
        $images = $this->service->getCurrentYearImages();

        $imagesData = AnnouncementResource::collection($images)->response()->getData(true)['data'];

        return $this->dataResponse([
            'count' => count($imagesData),
            'images' => $imagesData
        ], 200);
    }

    public function getCurrentYearFiles(): JsonResponse
    {
        $files = $this->service->getCurrentYearFiles();

        $filesData = AnnouncementResource::collection($files)->response()->getData(true)['data'];

        return $this->dataResponse([
            'count' => count($filesData),
            'files' => $filesData
        ], 200);
    }

    public function getLatestAnnouncements(int $limit = 7): JsonResponse
    {
        $announcements = $this->service->getLatestAnnouncements($limit);

        $announcementsData = AnnouncementResource::collection($announcements)
            ->response()
            ->getData(true)['data'];

        return $this->dataResponse([
            'count' => count($announcementsData),
            'announcements' => $announcementsData
        ], 200);
    }

    public function getAdminImages(): JsonResponse
    {
        $images = $this->service->getAdminImages();
        return $this->dataResponse([
            'count' => $images->count(),
            'data' => DoctorAnnouncementResource::collection($images),
        ], 200);
    }

    public function getAdminFiles(): JsonResponse
    {
        $files = $this->service->getAdminFiles();
        return $this->dataResponse([
            'count' => $files->count(),
            'data' => DoctorAnnouncementResource::collection($files),
        ], 200);
    }

    public function getLatestImages(): JsonResponse
    {
        $images = $this->service->getLatestImages();
        return $this->dataResponse([
            'count' => $images->count(),
            'data' => DoctorAnnouncementResource::collection($images),
        ], 200);
    }

    public function getLatestFiles(): JsonResponse
    {
        $files = $this->service->getLatestFiles();
        return $this->dataResponse([
            'count' => $files->count(),
            'data' => DoctorAnnouncementResource::collection($files),
        ], 200);
    }

    public function images(): JsonResponse
    {
        $images = $this->service->getImagesForStudents();
        return $this->dataResponse([
            'count' => $images->count(),
            'data' => DoctorAnnouncementResource::collection($images),
        ]);
    }

    public function files(): JsonResponse
    {
        $files = $this->service->getFilesForStudents();
        return $this->dataResponse([
            'count' => $files->count(),
            'data' => DoctorAnnouncementResource::collection($files),
        ]);
    }

    public function latestImages(): JsonResponse
    {
        $images = $this->service->getLatestImagesForStudents();
        return $this->dataResponse([
            'count' => $images->count(),
            'data' => DoctorAnnouncementResource::collection($images),
        ]);
    }

    public function latestFiles(): JsonResponse
    {
        $files = $this->service->getLatestFilesForStudents();
        return $this->dataResponse([
            'count' => $files->count(),
            'data' => DoctorAnnouncementResource::collection($files),
        ]);
    }

    public function lastYearImages(): JsonResponse
    {
        $images = $this->service->getLastYearImages();
        return $this->dataResponse([
            'count' => $images->count(),
            'data' => DoctorAnnouncementResource::collection($images),
        ]);
    }

    public function lastYearFiles(): JsonResponse
    {
        $files = $this->service->getLastYearFiles();
        return $this->dataResponse([
            'count' => $files->count(),
            'data' => DoctorAnnouncementResource::collection($files),
        ]);
    }

    public function showAllImageAnnouncements(): JsonResponse
    {
        $data = $this->service->getAllAnnouncements(AnnouncementType::Image->value);

        return $this->dataResponse($data ,200);
    }

    public function showAllFileAnnouncements(): JsonResponse
    {
        $data = $this->service->getAllAnnouncements(AnnouncementType::File->value);

        return $this->dataResponse($data ,200);
    }

    public function showProfessorImageAnnouncements(): JsonResponse
    {
        $data = $this->service->getAllAnnouncements(AnnouncementType::Image->value , AnnouncementAudience::Professors->value);

        return $this->dataResponse($data ,200);
    }

    public function showProfessorFileAnnouncements(): JsonResponse
    {
        $data = $this->service->getAllAnnouncements(AnnouncementType::File->value , AnnouncementAudience::Professors->value);

        return $this->dataResponse($data ,200);
    }

    public function doctorDownloadAnnouncement(int $announcementId): BinaryFileResponse
    {
        return $this->service->downloadAnnouncement($announcementId);
    }
}
