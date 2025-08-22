<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable , SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'university_number',
        'role',
        'email_verified_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => UserRole::class ,
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function Profile(): HasOne
    {
        return $this->hasOne(Profile::class, 'user_id', 'id');
    }

    public function OtpCodes(): HasMany
    {
        return $this->hasMany(OtpCode::class , 'user_id' , 'id');
    }

    public function Announcements(): BelongsToMany
    {
        return $this->belongsToMany(Announcement::class, 'favorites', 'user_id', 'announcement_id')
            ->withTimestamps();
    }

    public function sendInvitations(): HasMany
    {
        return $this->hasMany(GroupInvitation::class , 'invited_by_user_id' , 'id');
    }

    public function receivedInvitations(): HasMany
    {
        return $this->hasMany(GroupInvitation::class , 'invited_user_id' , 'id');
    }

    public function groupMember(): HasOne
    {
        return $this->hasOne(GroupMember::class , 'user_id' , 'id');
    }

    public function projectForms(): HasMany
    {
        return $this->hasMany(ProjectForm::class , 'user_id' , 'id');
    }

    public function joinRequest(): HasMany
    {
        return $this->hasMany(JoinRequest::class , 'user_id' , 'id');
    }

    public function doctorInquiries(): HasMany
    {
        return $this->hasMany(DoctorInquiry::class , 'doctor_id' , 'id');
    }

    public function formSignature(): HasOne
    {
        return $this->hasOne(FormSignature::class , 'user_id' , 'id');
    }

    public function supervisor(): HasOne
    {
        return $this->hasOne(InterviewCommittee::class , 'supervisor_id' , 'id');
    }

    public function member(): HasOne
    {
        return $this->hasOne(InterviewCommittee::class , 'member_id' , 'id');
    }

    public function gradeException(): HasOne
    {
        return $this->hasOne(GradeException::class , 'student_id' , 'id');
    }

    public function FirstPartyConversation(): HasMany
    {
        return $this->hasMany(Conversation::class , 'user_one_id' , 'id');
    }

    public function secondPartyConversation(): HasMany
    {
        return $this->hasMany(Conversation::class , 'user_two_id' , 'id');
    }

    public function Messages(): HasMany
    {
        return $this->hasMany(Message::class , 'sender_id' , 'id');
    }

    public function fcmTokens(): HasMany
    {
        return $this->hasMany(FcmToken::class , 'user_id' , 'id');
    }
}
