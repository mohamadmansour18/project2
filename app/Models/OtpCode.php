<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OtpCode extends Model
{
    use HasFactory;

    protected $table = 'otp_codes';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'otp_code',
        'expires_at',
        'is_used',
        'purpose',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class , 'user_id' , 'id')->withDefault();
    }

    public static function createOtpFor(int $userId , string $purpose): self
    {
        return self::create([
            'user_id' => $userId ,
            'otp_code' => random_int(100000 , 999999),
            'expires_at' => now()->addMinutes(5),
            'is_used' => false ,
            'purpose' => $purpose
        ]);
    }

}
