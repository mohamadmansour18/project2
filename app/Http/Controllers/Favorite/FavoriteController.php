<?php

namespace App\Http\Controllers\Favorite;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoctorAnnouncementResource;
use App\Models\Announcement;
use App\Services\FavoriteService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

class FavoriteController extends Controller
{
    use ApiSuccessTrait ;

    public function __construct(protected FavoriteService $service) {}

    public function imageFavorites(): JsonResponse
    {
        $favorites = $this->service->getUserImageFavorites(auth()->user());

        return $this->dataResponse([
            'count' => $favorites->count(),
            'data' => DoctorAnnouncementResource::collection($favorites)
        ]);
    }

    public function fileFavorites(): JsonResponse
    {
        $favorites = $this->service->getUserFileFavorites(auth()->user());

        return $this->dataResponse([
            'count' => $favorites->count(),
            'data' => DoctorAnnouncementResource::collection($favorites)
        ]);
    }

    public function store(Announcement $announcement): JsonResponse
    {
        $this->service->addFavorite(auth()->user(), $announcement);

        return $this->successResponse('الاضافة الى المفضلة', 'تمت الإضافة للمفضلة بنجاح', 201);
    }

    public function destroy(Announcement $announcement): JsonResponse
    {
        $this->service->removeFavorite(auth()->user(), $announcement);

        return $this->successResponse('الازالة من المفضلة', 'تمت الازالة من المفضلة بنجاح', 201);
    }
}
