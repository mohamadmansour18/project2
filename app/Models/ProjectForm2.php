<?php

namespace App\Models;

use App\Enums\ProjectForm2Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectForm2 extends Model
{
    use HasFactory;

    protected $table = 'project_form2s';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id' ,
        'arabic_project_title' ,
        'user_segment' ,
        'development_procedure' ,
        'libraries_and_tools' ,
        'roadmap_file' ,
        'work_plan_file' ,
        'filled_form_file_path' ,
        'status' ,
        'submission_date' ,
    ];

    protected $casts = [
        'status' => ProjectForm2Status::class
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }
}
