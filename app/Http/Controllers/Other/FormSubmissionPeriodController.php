<?php

namespace App\Http\Controllers\Other;

use App\Enums\FormSubmissionPeriodFormName;
use App\Exceptions\ProjectManagementException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateFormRequest;
use App\Http\Requests\CreateInterviewRequest;
use App\Http\Requests\UpdateFormRequest;
use App\Http\Requests\UpdateInterviewRequest;
use App\Services\DashBoard_Services\ProjectManagementService;
use App\Services\HomeMobileService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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

        return $this->successResponse('! تم هذا الاجراء بنجاح' , 'ان عملية تحديد مواعيد للاستمارة واحد تمت بنجاح' , 201);
    }

    public function updateForm1(int $formId , UpdateFormRequest $request): JsonResponse
    {
        $this->projectManagementService->updateForm($formId , $request->validated());

        return $this->successResponse('! تم هذا الاجراء بنجاح' , 'ان عملية تعديل مواعيد للاستمارة واحد تمت بنجاح' , 200);
    }

    public function deleteForm1(int $formId): JsonResponse
    {
        $this->projectManagementService->forceDeleteForm($formId);

        return $this->successResponse('! تم هذا الاجراء بنجاح' , 'ان عملية حذف مواعيد للاستمارة واحد تمت بنجاح' , 200);
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

        return $this->successResponse('! تم هذا الاجراء بنجاح' , 'ان عملية تحديد مواعيد للاستمارة اثنان تمت بنجاح' , 201);
    }

    public function updateForm2(int $formId , UpdateFormRequest $request): JsonResponse
    {
        $this->projectManagementService->updateForm($formId , $request->validated());

        return $this->successResponse('! تم هذا الاجراء بنجاح' , 'ان عملية تعديل مواعيد للاستمارة اثنان تمت بنجاح' , 200);
    }

    public function deleteForm2(int $formId): JsonResponse
    {
        $this->projectManagementService->forceDeleteForm($formId);

        return $this->successResponse('! تم هذا الاجراء بنجاح' , 'ان عملية حذف مواعيد للاستمارة اثنان تمت بنجاح' , 200);
    }

    public function getForm2(): JsonResponse
    {
        $data = $this->projectManagementService->getForm(FormSubmissionPeriodFormName::Form2->value);

        return $this->dataResponse($data , 200);
    }

    //////////////////////////////////////////////////////////////////////////////////

    public function createFormInterview(CreateInterviewRequest $request): JsonResponse
    {
        $this->projectManagementService->createInterview($request->validated());

        return $this->successResponse('! تم هذا الاجراء بنجاح' , 'ان عملية تحديد مواعيد المقابلات النهائية تم بنجاح' , 201);
    }

    public function updateFormInterview(UpdateInterviewRequest $request , int $interPeriodId): JsonResponse
    {
        $this->projectManagementService->updateInterview($request->validated() , $interPeriodId);

        return $this->successResponse('! تم تعديل موعد المقابلات النهائية بنجاح' , 'ان عملية تعديل مواعيد المقابلة النهائية تم بنجاح' , 200);
    }

    public function deleteFormInterview(int $periodId): JsonResponse
    {
        $this->projectManagementService->deleteInterview($periodId);

        return $this->successResponse('! تم هذا الاجراء بنجاح' , 'ان عملية حذف مواعيد المقابلات النهائية تم بنجاح' , 200);
    }

    public function getFormInterview(): JsonResponse
    {
        $data = $this->projectManagementService->getInterview();

        return response()->json($data, 200);
    }

    //////////////////////////////////////////////////////////////////////////////////
     public function generateAndDownload(): BinaryFileResponse|JsonResponse
     {
         return $this->projectManagementService->generateAndDownloadFormsDate();
     }
}
