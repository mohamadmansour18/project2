<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorInquiry extends Model
{
    use HasFactory;

    protected $table = 'doctor_inquiries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'doctor_id' ,
        'question' ,
        'answer' ,
        'is_answered' ,
        'answered_at' ,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class , 'doctor_id' , 'id')->withDefault();
    }
}
