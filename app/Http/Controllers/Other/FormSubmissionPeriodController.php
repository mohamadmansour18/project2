<?php

namespace App\Http\Controllers\Other;

use App\Enums\FormSubmissionPeriodFormName;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateFormRequest;
use App\Http\Requests\UpdateFormRequest;
use App\Services\DashBoard_Services\ProjectManagementService;
use App\Services\HomeMobileService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

class FormSubmissionPeriodController extends Controller
{
    use ApiSuccessTrait ;


    public function __construct(
        protected HomeMobileService $homeMobileService,
        protected ProjectManagementService $projectManagementService,
    )
    {}

    public function getFormDateForDoctor(): JsonResponse
    {

        $data = $this->homeMobileService->getAllFormPeriods();

        return $this->dataResponse( $data , 200);
    }

    public function getFormDataForStudent(): JsonResponse
    {
        $data = $this->homeMobileService->getAllFormPeriods();
        $value = $data['form1'];
        array_pop($data);
        $data['joinRequests'] = $value ;

        return $this->dataResponse($data , 200);

    }

    public function createForm1(CreateFormRequest $request): JsonResponse
    {
        $this->projectManagementService->createForm($request->validated() , FormSubmissionPeriodFormName::Form1->value);

        return $this->successResponse('تم هذا الاجراء بنجاح !' , 'ان عملية تحديد مواعيد للاستمارة واحد تمت بنجاح' , 201);
    }

    public function updateForm1(int $formId , UpdateFormRequest $request): JsonResponse
    {
        $this->projectManagementService->updateForm($formId , $request->validated());

        return $this->successResponse('تم هذا الاجراء بنجاح !' , 'ان عملية تعديل مواعيد للاستمارة واحد تمت بنجاح' , 200);
    }

    public function deleteForm1(int $formId): JsonResponse
    {
        $this->projectManagementService->forceDeleteForm($formId);

        return $this->successResponse('تم هذا الاجراء بنجاح !' , 'ان عملية حذف مواعيد للاستمارة واحد تمت بنجاح' , 200);
    }

    public function getForm1(): JsonResponse
    {
        $data = $this->projectManagementService->getForm(FormSubmissionPeriodFormName::Form1->value);

        return $this->dataResponse($data , 200);
    }

    //////////////////////////////////////////////////////////////////////////////////

    public function createForm2(CreateFormRequest $request): JsonResponse
    {
        $this->projectManagementService->createForm($request->validated() , FormSubmissionPeriodFormName::Form2->value);

        return $this->successResponse('تم هذا الاجراء بنجاح !' , 'ان عملية تحديد مواعيد للاستمارة اثنان تمت بنجاح' , 201);
    }

    public function updateForm2(int $formId , UpdateFormRequest $request): JsonResponse
    {
        $this->projectManagementService->updateForm($formId , $request->validated());

        return $this->successResponse('تم هذا الاجراء بنجاح !' , 'ان عملية تعديل مواعيد للاستمارة اثنان تمت بنجاح' , 200);
    }

    public function deleteForm2(int $formId): JsonResponse
    {
        $this->projectManagementService->forceDeleteForm($formId);

        return $this->successResponse('تم هذا الاجراء بنجاح !' , 'ان عملية حذف مواعيد للاستمارة اثنان تمت بنجاح' , 200);
    }

    public function getForm2(): JsonResponse
    {
        $data = $this->projectManagementService->getForm(FormSubmissionPeriodFormName::Form2->value);

        return $this->dataResponse($data , 200);
    }
}
