<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InterviewSchedule extends Model
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

    public function committee(): BelongsTo
    {
        return $this->belongsTo(InterviewCommittee::class, 'committee_id', 'id')->withDefault();
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id')->withDefault();
    }
}
