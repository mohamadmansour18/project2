<?php

namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;
use App\Services\HomeMobileService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    use ApiSuccessTrait ;
    public function __construct(
        protected HomeMobileService $homeMobileService
    ) {}

    public function getDoctorHomeStatistics(): JsonResponse
    {
        $data = $this->homeMobileService->getHomeStatistics();

        return $this->dataResponse(['data' => $data] ,200);
    }
}
