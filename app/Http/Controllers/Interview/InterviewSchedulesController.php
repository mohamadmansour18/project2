<?php

namespace App\Http\Controllers\Interview;

use App\Http\Controllers\Controller;
use App\Services\InterviewScheduleService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InterviewSchedulesController extends Controller
{
    use ApiSuccessTrait;

    public function __construct(
        protected InterviewScheduleService $service
    ) {}

    public function generateAssignAndDownload(): BinaryFileResponse|JsonResponse
    {
        return $this->service->generateAssignAndDownload();
    }

    public function deleteFinalInterview(): JsonResponse
    {
        return $this->service->deleteCurrentYearAssets();
    }
}
