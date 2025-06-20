<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterviewPeriod extends Model
{
    use HasFactory;

    protected $table = 'interview_periods';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'start_date' ,
        'end_date' ,
        'days' ,
        'duration' ,
    ];

    protected $casts = [
        'days' => 'array'
    ];
}
