<?php

namespace App\Models;

use App\Enums\InterviewSchedulesInterviewDay;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewSchedules extends Model
{
    use HasFactory;

    protected $table = 'interview_schedules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'committee_id' ,
        'group_id' ,
        'interview_day' ,
        'interview_time' ,
        'interview_end_time' ,
    ];

    protected $casts = [
        'interview_day' => 'array' ,
    ];
}
