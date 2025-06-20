<?php

namespace App\Models;

use App\Enums\GroupInvitationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
