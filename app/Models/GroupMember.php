<?php

namespace App\Models;

use App\Enums\GroupMemberRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_id' , 'id')->withDefault();
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id')->withDefault();
    }
}
