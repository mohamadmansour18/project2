<?php

namespace App\Http\Controllers\Interview;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateInterviewCommitteeRequest;
use App\Http\Requests\DoctorSearchRequest;
use App\Services\DashBoard_Services\ProjectManagementService;
use App\Services\InterviewCommitteeService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InterviewCommitteeController extends Controller
{
    use ApiSuccessTrait ;
    public function __construct(
        protected InterviewCommitteeService $interviewCommitteeService,
        protected ProjectManagementService $projectManagementService
    )
    {}

    public function showDoctorInterviewGroup(): JsonResponse
    {
        $data = $this->interviewCommitteeService->getDoctorCommitteeGroupsData();

        return response()->json($data , 200);
    }

    public function showDoctorInterviewGroupSearched(DoctorSearchRequest $request): JsonResponse
    {
        $data = $this->interviewCommitteeService->searchDoctorCommitteeGroupsData($request->search);

        return response()->json($data , 200);
    }

    public function showAvailableDoctorsNotInCommittee(): JsonResponse
    {
        $data = $this->projectManagementService->getAvailableDoctorsNotInCommittee();

        return response()->json($data , 200);
    }

    public function createInterviewCommittee(CreateInterviewCommitteeRequest $request): JsonResponse
    {
        $this->projectManagementService->createCommittee($request->doctor1_id, $request->doctor2_id, $request->supervisor_id);

        return $this->successResponse('تمت العملية بنجاح !' , 'تم انشاء لجنة المقابلة المحددة بنجاح' , 201);
    }

    public function getInterviewCommittee(): JsonResponse
    {
        $committees = $this->projectManagementService->getCommitteesForCurrentYear();

        return response()->json($committees, 200);
    }

    public function deleteInterviewCommittee(int $committeeId): JsonResponse
    {
        $this->projectManagementService->deleteCommittee($committeeId);

        return $this->successResponse('تمت العملية بنجاح !' , 'تم حذف اللجنة المحددة ' , 200);
    }

    public function notifyCommittee(): JsonResponse
    {
        $this->projectManagementService->notifyInterviewCommitteeDoctors();

        return $this->successResponse('تمت العملية بنجاح !' , 'تم ارسال اشعارات للدكاترة في جميع لجان المقابلة للسنة الحالية' , 200);
    }

    public function generateAndDownloadCommittee(): BinaryFileResponse|JsonResponse
    {
        return $this->projectManagementService->generateAndDownloadCommittee();
    }
}
