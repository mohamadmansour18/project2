<?php

namespace App\Http\Controllers\Grade;

use App\Http\Controllers\Controller;
use App\Http\Requests\GroupGradeRequest;
use App\Http\Requests\UpdateGroupGradeRequest;
use App\Services\GroupGradeService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

class ProjectGradeController extends Controller
{
    use ApiSuccessTrait;
    public function __construct(
        protected GroupGradeService $groupGradeService,
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

}
