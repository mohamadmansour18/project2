<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectGrade extends Model
{
    use HasFactory;

    protected $table = 'project_grades';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'committee_id' ,
        'group_id' ,
        'presentation_grade' ,
        'project_grade' ,
        'total_grade' ,
        'is_edited' ,
        'previous_total_grade' ,
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
