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

    public function isFormPeriodActive(string $formName): bool
    {
        $period = FormSubmissionPeriod::where('form_name', $formName)
            ->whereYear('start_date', now()->year)
            ->first();

        if (!$period) {
            return false;
        }

        return now()->between($period->start_date, $period->end_date);
    }

    public function existsFormForCurrentYear(string $formName): bool
    {
        $currentYear = now()->year;

        return FormSubmissionPeriod::query()
            ->whereYear('created_at' , $currentYear)
            ->where('form_name' , $formName)
            ->exists();
    }

    public function getFormForCurrentYear(string $formName): ? FormSubmissionPeriod
    {
        $year = now()->year;

        return FormSubmissionPeriod::select('id', 'start_date', 'end_date')
            ->whereYear('created_at' , $year)
            ->where('form_name' , $formName)
            ->first();
    }

    public function createForm(array $data): FormSubmissionPeriod
    {
        $form = new FormSubmissionPeriod($data);
        $form->save();
        return $form;
    }

    public function updateForm(FormSubmissionPeriod $form , array $data): FormSubmissionPeriod
    {
        $form->fill($data);
        $form->save();
        return $form;
    }

    public function deleteForm(FormSubmissionPeriod $form): void
    {
        $form->forceDelete();
    }

    public function findById(int $formId)
    {
        return FormSubmissionPeriod::find($formId);
    }


}
