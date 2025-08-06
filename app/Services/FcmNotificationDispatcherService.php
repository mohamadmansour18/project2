<?php

namespace App\Services;

use App\Jobs\SendMultiFcmNotificationJob;
use App\Jobs\SendSingleFcmNotificationJob;
use App\Jobs\StoreMultiNotificationJob;
use App\Models\User;
use App\Notifications\FcmDatabaseNotification;
use Illuminate\Support\Collection;

class FcmNotificationDispatcherService
{
    public function sendToUsers(Collection $users ,  string $title, string $body): void
    {
        //store notification
        $users->chunk(50)->each(function ($chunk) use ($title , $body){
            StoreMultiNotificationJob::dispatch($chunk , $title , $body);
        });

        //send notification
        $users->chunk(50)->each(function($chunk) use($title, $body) {
            SendMultiFcmNotificationJob::dispatch($chunk , $title , $body);
        });
    }

    public function sendToUser(User $user, string $title, string $body): void
    {
        $user->notify(new FcmDatabaseNotification($title , $body));

        SendSingleFcmNotificationJob::dispatch($user, $title, $body);
    }
}
