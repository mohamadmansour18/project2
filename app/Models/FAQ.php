<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FAQ extends Model
{
    use HasFactory;

    protected $table = 'f_a_q_s';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'question' ,
        'answer' ,
    ];

    protected $casts = [

    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'faq_id', 'id');
    }
}
