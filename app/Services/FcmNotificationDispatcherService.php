<?php

namespace App\Services;

use App\Jobs\SendMultiFcmNotificationJob;
use App\Jobs\SendSingleFcmNotificationJob;
use App\Models\User;
use Illuminate\Support\Collection;

class FcmNotificationDispatcherService
{
    public function sendToUsers(Collection $users ,  string $title, string $body): void
    {
        $users->chunk(50)->each(function($chunk) use($title, $body) {
            SendMultiFcmNotificationJob::dispatch($chunk , $title , $body);
        });
    }

    public function sendToUser(User $user, string $title, string $body): void
    {
        SendSingleFcmNotificationJob::dispatch($user, $title, $body);
    }
}
