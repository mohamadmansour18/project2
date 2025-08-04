<?php

namespace App\Services;

use App\Jobs\SendMultiFcmNotification;
use Illuminate\Support\Collection;

class FcmNotificationDispatcherService
{
    public function sendToUsers(Collection $users ,  string $title, string $body): void
    {
        $users->chunk(50)->each(function($chunk) use($title, $body) {
            SendMultiFcmNotification::dispatch($chunk , $title , $body);
        });
    }
}
