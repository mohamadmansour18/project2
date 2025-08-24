<?php

namespace App\Http\Controllers\Interview;

use App\Http\Controllers\Controller;
use App\Services\DashBoard_Services\HomeDashBoardService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

class InterviewPeriodController extends Controller
{
    use ApiSuccessTrait ;

    public function __construct(
        protected HomeDashBoardService $homeDashBoardService
    )
    {}

    public function getCommitteesAndPeriods(): JsonResponse
    {
        $data = $this->homeDashBoardService->getCommitteesAndPeriods();

        return $this->dataResponse($data , 200);
    }
}
