<?php

namespace App\Http\Controllers\FormOne;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectFormRequest;
use App\Models\ProjectForm;
use App\Services\ProjectFormService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

class ProjectFormController extends Controller
{
    use ApiSuccessTrait ;

    public function __construct(
        protected ProjectFormService $service
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
}
