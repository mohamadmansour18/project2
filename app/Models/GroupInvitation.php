<?php

namespace App\Models;

use App\Enums\GroupInvitationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupInvitation extends Model
{
    use HasFactory;

    protected $table = 'group_invitations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id' ,
        'invited_user_id' ,
        'invited_by_user_id' ,
        'status'
    ];

    protected $casts = [
        'status' => GroupInvitationStatus::class ,
    ];

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class , 'invited_by_user_id' , 'id')->withDefault();
    }

    public function invitedUser(): BelongsTo
    {
        return $this->belongsTo(User::class , 'invited_user_id' , 'id')->withDefault();
    }
}
