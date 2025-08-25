<?php

namespace App\Services;

use App\Repositories\NotificationsRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class notificationsService
{
    public function __construct(
        protected NotificationsRepository $notificationsRepository,
    )
    {}

    public function getNotifications(): array
    {
        $userId = Auth::id();

        $notifications = $this->notificationsRepository->getUserNotifications($userId);

        $formatted = $notifications->map(function ($notification) {
            return [
                'id'    => $notification->id,
                'title' => $notification->data['title'] ?? '',
                'body'  => $notification->data['body'] ?? '',
                'date'  => Carbon::parse($notification->created_at)->diffForHumans(),
            ];
        })->toArray();

        $unreadIds = $notifications->whereNull('read_at')->pluck('id')->toArray();
        if(!empty($unreadIds))
        {
            $this->notificationsRepository->markAsRead($unreadIds);
        }

        return $formatted;
    }

    public function countUnread(): int
    {
        $userId = Auth::id();

        return $this->notificationsRepository->countUnread($userId);
    }
}
