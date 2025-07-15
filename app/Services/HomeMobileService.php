<?php

namespace App\Services;

use App\Repositories\FormSubmissionPeriodRepository;
use Carbon\Carbon;

class HomeMobileService
{
    protected FormSubmissionPeriodRepository $formRepository ;
    public function __construct(FormSubmissionPeriodRepository $formRepository)
    {
        $this->formRepository = $formRepository ;
    }

    public function getFormPeriodData(string $formName): array
    {
        $period = $this->formRepository->getByFormName($formName);

        if(!$period)
        {
            return[
              'start' => '--:--:--' ,
              'end' => '--:--:--' ,
              'remaining' => '--'
            ];
        }

        return [
            'start' => $period->start_date->format('Y-m-d'),
            'end' => $period->end_date->format('Y-m-d'),
            'remaining' => $this->formatRemainingTime($period->end_date),
        ];
    }

    private function formatRemainingTime(Carbon $endDate): string
    {
        $now = now();

        if($now->greaterThanOrEqualTo($endDate)){
            return '--';
        }

        $diffInMinutes = $now->diffInMinutes($endDate);
        $diffInHours = $now->diffInHours($endDate);
        $diffInDays = $now->diffInDays($endDate);
        $diffInMonths = $now->diffInMonths($endDate);

        if($diffInDays > 30)
        {
            return ' أشهر' . $diffInMonths ;
        }

        if($diffInDays >= 1)
        {
            return ' يوم' . $diffInDays ;
        }

        if($diffInHours >= 1)
        {
            return ' ساعة' . $diffInHours ;
        }

        return ' دقيقة' . $diffInMinutes ;
    }
}
