<?php

namespace App\Repositories;

use App\Enums\FormSubmissionPeriodFormName;
use App\Models\FormSubmissionPeriod;

class FormSubmissionPeriodRepository
{
    public function getAllCurrentYearForms(): \Illuminate\Support\Collection
    {
        $currentYear = now()->year ;

        return FormSubmissionPeriod::whereYear('start_date' , $currentYear)
            ->whereIn('form_name' , FormSubmissionPeriodFormName::convertEnumToArray())
            ->get()
            ->keyBy(fn($item) => $item->form_name->value);
    }
}
