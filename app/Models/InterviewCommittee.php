<?php

namespace App\Models;

use App\Enums\InterviewCommitteeDays;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewCommittee extends Model
{
    use HasFactory;

    protected $table = 'interview_committees';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'supervisor_id' ,
        'member_id' ,
        'days' ,
        'start_interview_time',
        'end_interview_time',
    ];

    protected $casts = [
        'days' => 'array' ,
    ];
}
