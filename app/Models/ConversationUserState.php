<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConversationUserState extends Model
{
    use HasFactory;

    protected $table = 'conversation_user_states';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id' ,
        'user_id' ,
        'last_read_at' ,
    ];

    protected $casts = [

    ];
}
