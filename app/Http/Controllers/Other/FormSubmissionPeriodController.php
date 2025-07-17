<?php

namespace App\Http\Controllers\Other;

use App\Enums\FormSubmissionPeriodFormName;
use App\Http\Controllers\Controller;
use App\Services\HomeMobileService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

class FormSubmissionPeriodController extends Controller
{
    use ApiSuccessTrait ;
    protected HomeMobileService $homeMobileService ;

    public function __construct(HomeMobileService $homeMobileService)
    {
        $this->homeMobileService = $homeMobileService;
    }

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


}
