<?php

namespace App\Repositories;

use App\Models\FormSubmissionPeriod;

class FormSubmissionPeriodRepository
{
    public function getByFormName(string $formName): ?FormSubmissionPeriod
    {
        $currentYear = now()->year ;

        return FormSubmissionPeriod::where('form_name' , $formName)
            ->whereYear('start_date' , $currentYear)
            ->first();
    }
}
