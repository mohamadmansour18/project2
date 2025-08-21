<?php

namespace App\Observers;

use App\Models\FormSubmissionPeriod;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FormSubmissionPeriodObserver
{
    /**
     * Handle the FormSubmissionPeriod "created" event.
     */
    public function created(FormSubmissionPeriod $formSubmissionPeriod): void
    {
        Cache::forget('home_form_periods');
    }

    /**
     * Handle the FormSubmissionPeriod "updated" event.
     */
    public function updated(FormSubmissionPeriod $formSubmissionPeriod): void
    {
        Cache::forget('home_form_periods');
    }

    /**
     * Handle the FormSubmissionPeriod "deleted" event.
     */
    public function deleted(FormSubmissionPeriod $formSubmissionPeriod): void
    {
        Cache::forget('home_form_periods');
        Log::info('hello from delete observer');
    }

    /**
     * Handle the FormSubmissionPeriod "restored" event.
     */
    public function restored(FormSubmissionPeriod $formSubmissionPeriod): void
    {
        //
    }

    /**
     * Handle the FormSubmissionPeriod "force deleted" event.
     */
    public function forceDeleted(FormSubmissionPeriod $formSubmissionPeriod): void
    {
        Cache::forget('home_form_periods');
    }
}
