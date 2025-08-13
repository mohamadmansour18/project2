<?php

namespace App\Http\Controllers\FormTwo;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectForm2Request;
use App\Models\ProjectForm2;
use App\Services\ProjectForm2Service;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

class ProjectForm2Controller extends Controller
{
    use ApiSuccessTrait;

    public function __construct(
        protected ProjectForm2Service $service
    ) {}

    public function store(StoreProjectForm2Request $request): JsonResponse
    {
        $this->service->store($request->validated());
        return $this->successResponse('تم الحفظ', 'تم إنشاء الاستمارة 2 بنجاح.', 201);
    }

    public function download(ProjectForm2 $form)
    {
        return $this->service->downloadFilledForm($form);
    }

    public function preview(ProjectForm2 $form): JsonResponse
    {
        $pdfData = $this->service->getPreviewPdfBase64($form);
        return $this->dataResponse([
            'PDF' => $pdfData,
        ]);
    }
}
