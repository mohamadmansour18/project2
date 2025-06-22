<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSignature extends Model
{
    use HasFactory;

    protected $table = 'form_signatures';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id' ,
        'project_form_id' ,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_id' , 'id')->withDefault();
    }

    public function projectForm(): BelongsTo
    {
        return $this->belongsTo(ProjectForm::class, 'project_form_id', 'id')->withDefault();
    }
}
