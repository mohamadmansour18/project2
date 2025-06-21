<?php

namespace App\Models;

use App\Enums\MessageType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'conversation_id' ,
        'sender_id' ,
        'faq_id' ,
        'message_type' ,
        'content' ,
    ];

    protected $casts = [
        'message_type' => MessageType::class ,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class , 'sender_id' , 'id')->withDefault();
    }
}
