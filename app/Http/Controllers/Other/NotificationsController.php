<?php

namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;
use App\Services\notificationsService;
use Illuminate\Http\JsonResponse;


class NotificationsController extends Controller
{
    public function __construct(
        protected notificationsService $notificationsService,
    )
    {}

    public function getNotifications(): JsonResponse
    {
        $data = $this->notificationsService->getNotifications();

        return response()->json($data, 200);
    }

    public function unreadCount(): JsonResponse
    {
        $data = $this->notificationsService->countUnread();

        return response()->json(['count' => $data], 200);
    }
}
