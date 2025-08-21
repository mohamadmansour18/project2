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
use Illuminate\Database\Eloquent\Collection;
class SendMultiFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2 ;
    public $backoff = 10;

    protected Collection $users;
    protected string $title;
    protected string $body;

    /**
     * Create a new job instance.
     */
    public function __construct(Collection $users, string $title, string $body)
    {
        $this->users = $users;
        $this->title = $title;
        $this->body = $body;
    }

    /**
     * Execute the job.
     */
    public function handle(FirebaseNotificationService $fcm): void
    {
        foreach ($this->users as $user)
        {
            try{
                $tokens = $user->fcmTokens()->pluck('token')->toArray();

                if(empty($tokens))
                {
                    Log::info("المستخدم {$user->id} لا يملك FCM Tokens");
                    continue;
                }

                $fcm->send($this->title, $this->body, $tokens);

            }catch(\Throwable $exception)
            {
                Log::error("فشل إرسال إشعار للمستخدم{$user->id}: " . $exception->getMessage());
            }
        }
    }
}
