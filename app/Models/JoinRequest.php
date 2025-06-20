<?php

namespace App\Models;

use App\Enums\JoinRequestStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoinRequest extends Model
{
    use HasFactory;

    protected $table = 'join_requests';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id' ,
        'user_id' ,
        'status' ,
        'reviewed_at' ,
        'description'
    ];

    protected $casts = [
        'status' => JoinRequestStatus::class ,
    ];
}
