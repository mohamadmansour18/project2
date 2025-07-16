<?php

namespace App\Http\Controllers\Favorite;

use App\Http\Controllers\Controller;
use App\Services\HomeMobileService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

class AnnouncementsController extends Controller
{
    use ApiSuccessTrait ;
    protected HomeMobileService  $homeService;

    public function __construct(HomeMobileService $homeService)
    {
        $this->homeService = $homeService;
    }

    public function getAnnouncementStatistics(): JsonResponse
    {
        $response = $this->homeService->getAnnouncementStatistics();

        return $this->dataResponse($response , 200);
    }
}
