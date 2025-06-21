<?php

namespace App\Models;

use App\Enums\ConversationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Conversation extends Model
{
    use HasFactory;

    protected $table = 'conversations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_one_id' ,
        'user_two_id' ,
        'type' ,
    ];

    protected $casts = [
        'type' => ConversationType::class
    ];

    public function firstUser(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_one_id' , 'id')->withDefault();
    }

    public function secondUser(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_two_id' , 'id')->withDefault();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id', 'id');
    }
}
