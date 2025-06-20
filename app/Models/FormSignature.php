<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormSignature extends Model
{
    use HasFactory;

    protected $table = 'form_signatures';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id' ,
        'project_form_id' ,
    ];

}
