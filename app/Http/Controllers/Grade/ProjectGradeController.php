<?php

namespace App\Http\Controllers\Grade;

use App\Exceptions\ProjectManagementException;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupGradeRequest;
use App\Http\Requests\UpdateGroupGradeRequest;
use App\Services\DashBoard_Services\GradeManagementService;
use App\Services\GroupGradeService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProjectGradeController extends Controller
{
    use ApiSuccessTrait;
    public function __construct(
        protected GroupGradeService $groupGradeService,
        protected GradeManagementService $gradeManagementService,
    )
    {}

    public function insertGroupGrade(GroupGradeRequest $request): JsonResponse
    {
        $this->groupGradeService->createGrade($request->validated());

        return $this->successResponse('تمت العملية بنجاح !', 'تمت اضافة العلامة المحدد الى هذا الغروب',201);
    }

    public function updateGroupGrade(UpdateGroupGradeRequest $request): JsonResponse
    {
        $this->groupGradeService->updateGrade($request->validated());

        return $this->successResponse('تمت العملية بنجاح !' , 'تم تعديل علامة هذا الغروب المحدد بنجاح' , 200);
    }

    public function getGrades(): JsonResponse
    {
        $data = $this->gradeManagementService->getGradesForLastThreeYears();

        return response()->json($data,200);
    }

    /**
     * @throws ProjectManagementException
     */
    public function downloadGrades(): BinaryFileResponse|JsonResponse
    {
        return $this->gradeManagementService->generateAndDownloadGrade();
    }

}
