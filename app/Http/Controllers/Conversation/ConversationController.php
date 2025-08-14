<?php

namespace App\Http\Controllers\Conversation;

use App\Http\Controllers\Controller;
use App\Services\ConversationService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

class ConversationController extends Controller
{
    use ApiSuccessTrait;
    public function __construct(
        protected ConversationService $conversationService
    )
    {}

    public function showStudentConversations(): JsonResponse
    {
        $data = $this->conversationService->listUserConversations();

        return response()->json($data, 200);
    }
}
