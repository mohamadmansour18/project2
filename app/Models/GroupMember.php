<?php

namespace App\Models;

use App\Enums\GroupMemberRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    use HasFactory;

    protected $table = 'group_members';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id' ,
        'user_id' ,
        'role' ,
    ];

    protected $casts = [
        'role' => GroupMemberRole::class ,
    ];
}
