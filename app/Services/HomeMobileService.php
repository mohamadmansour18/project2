<?php

namespace App\Services;

use App\Models\FormSubmissionPeriod;
use App\Repositories\FormSubmissionPeriodRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class HomeMobileService
{
    protected FormSubmissionPeriodRepository $formRepository ;
    public function __construct(FormSubmissionPeriodRepository $formRepository)
    {
        $this->formRepository = $formRepository ;
    }

    public function getAllFormPeriods(): array
    {
        return Cache::rememberForever('home_form_periods', function () {
            $forms = $this->formRepository->getAllCurrentYearForms();

            return [
                'form1' => $this->formatForm($forms->get('form1')),
                'form2' => $this->formatForm($forms->get('form2')),
                'interview' => $this->formatForm($forms->get('interviews')),
            ];
        });

    }

    private function formatForm(?FormSubmissionPeriod $period): array
    {

        if(!$period)
        {
            return [
                'start' => '--:--:--' ,
                'end' => '--:--:--' ,
                'remaining' => '--' ,
            ];
        }

        return [
            'start' => $period->start_date->format('Y-m-d'),
            'end' => $period->end_date->format('Y-m-d'),
            'remaining' => $this->formatRemainingTime($period->end_date)
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
            return  $diffInMonths . 'أشهر';
        }

        if($diffInDays >= 1)
        {
            return  $diffInDays . 'يوم';
        }

        if($diffInHours >= 1)
        {
            return  $diffInHours . 'ساعة';
        }

        return  $diffInMinutes .  'دقيقة';
    }
}
