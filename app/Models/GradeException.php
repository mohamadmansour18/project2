<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GradeException extends Model
{
    use HasFactory;

    protected $table = 'grade_exceptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'grade_id' ,
        'student_id' ,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class , 'student_id' , 'id')->withDefault();
    }
}
