<?php

namespace App\Models;

use App\Enums\GroupSpecialityNeeded;
use App\Enums\GroupType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'image',
    ];

    protected $casts = [
        'speciality_needed' => 'array',
        'framework_needed' => 'array',
        'type' => GroupType::class,
    ];

    public function members(): HasMany
    {
        return $this->hasMany(GroupMember::class, 'group_id', 'id');
    }

    public function joinRequests(): HasMany
    {
        return $this->hasMany(JoinRequest::class, 'group_id', 'id');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(GroupInvitation::class, 'group_id', 'id');
    }

    public function projectForms(): HasMany
    {
        return $this->hasMany(ProjectForm::class, 'group_id', 'id');
    }

    public function interviewSchedules(): HasMany
    {
        return $this->hasMany(InterviewSchedule::class, 'group_id', 'id');
    }

    public function projectForm2(): HasOne
    {
        return $this->hasOne(ProjectForm2::class, 'group_id', 'id');
    }

    public function projectGrade(): HasOne
    {
        return $this->hasOne(ProjectGrade::class, 'group_id', 'id');
    }
}
