<?php

namespace App\Models;

use App\Enums\ProjectFormStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectForm extends Model
{
    use HasFactory;

    protected $table = 'project_forms';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id' ,
        'user_id' ,
        'arabic_title' ,
        'english_title' ,
        'description' ,
        'project_scope' ,
        'targeted_sector' ,
        'sector_classification' ,
        'stakeholders' ,
        'supervisor_signature' ,
        'filled_form_file_path' ,
        'submission_date' ,
        'status' ,
    ];

    protected $casts = [
        'status' => ProjectFormStatus::class ,
    ];

    public function users(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_id' , 'id')->withDefault();
    }
}
