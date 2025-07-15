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

    public function getFormDate(): JsonResponse
    {
        $form1 = $this->homeMobileService->getFormPeriodData(FormSubmissionPeriodFormName::Form1->value);

        return $this->dataResponse(['form1' => $form1] , 200);
    }
}
