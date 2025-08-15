<?php

namespace App\Http\Controllers\Conversation;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConversationSearchRequest;
use App\Services\ConversationService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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

    public function selectUserToStartConversation(): JsonResponse
    {
        $data = $this->conversationService->listUserOptionForConversations();

        return $this->dataResponse($data , 200);
    }

    public function createConversation(int $otherUserId): JsonResponse
    {
        $this->conversationService->createConversationBetweenUsers($otherUserId);

        return $this->successResponse('تمت العملية بنجاح !' , 'تم انشاء محادثة بينك وبين المستخدم المختار بنجاح' , 201);
    }

    public function searchConversation(ConversationSearchRequest $request): JsonResponse
    {
        $data = $this->conversationService->searchConversation($request->search);

        return response()->json($data, 200);
    }
}
