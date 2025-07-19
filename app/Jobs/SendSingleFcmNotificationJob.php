<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\FirebaseNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSingleFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $backoff = 5;

    protected User $user ;
    protected string $title ;
    protected string $body ;
    /**
     * Create a new job instance.
     */
    public function __construct(User $user, string $title, string $body)
    {
        $this->user = $user;
        $this->title = $title;
        $this->body = $body;
    }

    /**
     * Execute the job.
     */
    public function handle(FirebaseNotificationService $fcm): void
    {
        try {
            $tokens = $this->user->fcmTokens()->pluck('token')->toArray();

            if(empty($tokens))
            {
                Log::warning('This user dont have FCM token : ' . $this->user->id);
                return ;
            }

            $fcm->send($this->title, $this->body, $tokens);

        } catch (\Throwable $exception){
            Log::error(' فشل إرسال إشعار FCM', [
                'user_id' => $this->user->id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
