<?php

namespace App\Models;

use App\Enums\GroupSpecialityNeeded;
use App\Enums\GroupType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $table = 'groups';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name' ,
        'description' ,
        'speciality_needed' ,
        'framework_needed' ,
        'type' ,
        'qr_code' ,
        'number_of_members' ,
    ];

    protected $casts = [
        'speciality_needed' => GroupSpecialityNeeded::class ,
        'type' => GroupType::class ,
        'framework_needed' => 'array' ,
    ];
}
