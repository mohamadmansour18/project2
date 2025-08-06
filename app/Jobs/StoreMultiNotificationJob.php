<?php

namespace App\Jobs;

use App\Notifications\FcmDatabaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class StoreMultiNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Collection $users ;
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
    public function handle(): void
    {
        try{
            foreach ($this->users as $user)
            {
                $user->notify(new FcmDatabaseNotification($this->title, $this->body));
            }
        }catch (\Throwable $exception)
        {
            Log::error(" فشل تخزين الاشعار في النظام " . $exception->getMessage());
        }
    }
}
