<?php

namespace App\Models;

use App\Helpers\ArabicText;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;

class FAQ extends Model
{
    use HasFactory , Searchable;

    protected $table = 'f_a_q_s';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'question' ,
        'answer' ,
        'is_active'
    ];

    protected $casts = [

    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'faq_id', 'id');
    }

    public function searchableAs(): string
    {
        return 'faqs' ;
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'answer' => $this->answer,
            'is_active' => (bool) $this->is_active,
            'arr_question_normalized' => ArabicText::normalize($this->question)
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return (bool) $this->is_active;
    }

}
