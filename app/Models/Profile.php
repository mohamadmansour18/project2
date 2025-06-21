<?php

namespace App\Models;

use App\Enums\ProfileGovernorate;
use App\Enums\ProfileStudentSpeciality;
use App\Enums\ProfileStudentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    use HasFactory;
    protected $table = 'profiles';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id' ,
        'governorate' ,
        'birth_date' ,
        'phone_number' ,
        'profile_image' ,
        'student_speciality' ,
        'student_status' ,
        'signature'
    ];

    protected $casts = [
        'governorate' => ProfileGovernorate::class ,
        'student_speciality' => ProfileStudentSpeciality::class ,
        'student_status' => ProfileStudentStatus::class ,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_id' , 'id')->withDefault();
    }
}
