<?php

namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;
use App\Services\DashBoard_Services\HomeDashBoardService;
use App\Services\HomeMobileService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    use ApiSuccessTrait ;
    public function __construct(
        protected HomeMobileService $homeMobileService ,
        protected HomeDashBoardService $homeDashBoardService
    ) {}

    public function getHomeStatistics(): JsonResponse
    {
        $data = $this->homeMobileService->getHomeStatistics();

        return $this->dataResponse(['data' => $data] ,200);
    }

    public function getDoctorHomeGroupStatistics(): JsonResponse
    {
        $data = $this->homeMobileService->getGroupStatistics();

        return $this->dataResponse($data , 200);
    }


    //---------------------------< Dash Board >---------------------------//

    public function getCurdStatistics(): JsonResponse
    {
        $response = $this->homeDashBoardService->getHomeCurdCurrentYearStats();

        return $this->dataResponse($response , 200);
    }
}
