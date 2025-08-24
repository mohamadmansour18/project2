<?php

namespace App\Http\Controllers\FormOne;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectFormRequest;
use App\Models\ProjectForm;
use App\Services\DashBoard_Services\GroupManagementService;
use App\Services\ProjectFormService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjectFormController extends Controller
{
    use ApiSuccessTrait ;

    public function __construct(
        protected ProjectFormService $service,
        protected GroupManagementService $groupManagementService
    ) {}

    public function store(StoreProjectFormRequest $request): JsonResponse
    {
        $this->service->store($request->validated());

        return $this->successResponse('تعبئة الاستمارة' , 'تم تعبئة الاستمارة بنجاح.' , 201);
    }

    public function sign(ProjectForm $form): JsonResponse
    {
        $this->service->sign($form);

        return $this->successResponse('تم التوقيع', 'تم توقيع الاستمارة بنجاح.');
    }

    public function update(StoreProjectFormRequest $request, ProjectForm $form): JsonResponse
    {

        $this->service->update($form, $request->validated());

        return $this->successResponse('تم التعديل', 'تم تعديل بيانات الاستمارة بنجاح.');
    }

    public function submit(ProjectForm $form): JsonResponse
    {
        $this->service->submitToSupervisor($form);

        return $this->successResponse('تم الإرسال', 'تم إرسال الاستمارة للمشرف بنجاح.');
    }

    public function download(ProjectForm $form)
    {
        return $this->service->downloadFilledForm($form);
    }

    public function preview(ProjectForm $form): JsonResponse
    {
        $pdfData = $this->service->getPreviewPdfBase64($form);
        return $this->dataResponse([
            'PDF' => $pdfData,
        ]);
    }

    public function approveForm(int $formId): JsonResponse
    {
        $this->service->signForm($formId);

        return $this->successResponse('تمت العملية بنجاح !' , 'تم توقيع هذه الاستمارة بنجاح' , 200);
    }

    public function rejectForm(int $formId): JsonResponse
    {
        $this->service->rejectForm($formId);

        return $this->successResponse('تمت العملية بنجاح !' , 'تم رفض هذه الاستمارة بنجاح' , 200);
    }

    public function downloadForm(int $formId): BinaryFileResponse
    {
        return $this->service->downloadFormForDoctor($formId);
    }

    public function downloadFormWeb(int $groupId): StreamedResponse
    {
        return $this->groupManagementService->downloadForm1($groupId);
    }
}
