<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\DatabaseNotification;

class NotificationsRepository
{
    public function getUserNotifications(int $userId): Collection|array
    {
        return DatabaseNotification::query()
            ->where('notifiable_id', $userId)
            ->where('notifiable_type', 'App\\Models\\User')
            ->latest()
            ->get();
    }

    public function markAsRead(array $ids): void
    {
        DatabaseNotification::whereIn('id' , $ids)->update(['read_at' => now()]);
    }

    public function countUnread(int $userId): int
    {
        return DatabaseNotification::query()
            ->where('notifiable_type', 'App\\Models\\User')
            ->where('notifiable_id', $userId)
            ->whereNull('read_at')
            ->count();
    }
}
