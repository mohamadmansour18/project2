<?php

namespace App\Models;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $table = 'announcements';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title' ,
        'type' ,
        'attachment_path' ,
        'audience'
    ];

    protected $casts = [
        'type' => AnnouncementType::class ,
        'audience' => AnnouncementAudience::class ,
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'favorites', 'announcement_id', 'user_id')
            ->withTimestamps();
    }
}
