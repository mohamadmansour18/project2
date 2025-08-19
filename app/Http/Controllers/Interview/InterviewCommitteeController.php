<?php

namespace App\Http\Controllers\Interview;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorSearchRequest;
use App\Services\InterviewCommitteeService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

class InterviewCommitteeController extends Controller
{
    use ApiSuccessTrait ;
    public function __construct(
        protected InterviewCommitteeService $interviewCommitteeService
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
}
