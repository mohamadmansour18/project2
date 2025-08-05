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

    public function getCurrentInterviewPeriod(): ? FormSubmissionPeriod
    {
        return FormSubmissionPeriod::where('form_name' , FormSubmissionPeriodFormName::Interviews->value)
            ->whereYear('start_date' , now()->year)
            ->first();

    }

    public function isInForm1PeriodNow(): bool
    {
        return FormSubmissionPeriod::where('form_name', FormSubmissionPeriodFormName::Form1->value)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->exists();
    }
}
