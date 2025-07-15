<?php

namespace App\Models;

use App\Enums\FormSubmissionPeriodFormName;
use App\Observers\FormSubmissionPeriodObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy([FormSubmissionPeriodObserver::class])]

class FormSubmissionPeriod extends Model
{
    use HasFactory;

    protected $table = 'form_submission_periods';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'form_name' ,
        'start_date' ,
        'end_date' ,
    ];

    protected $casts = [
        'form_name' => FormSubmissionPeriodFormName::class ,
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];
}
