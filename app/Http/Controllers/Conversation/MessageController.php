<?php

namespace App\Http\Controllers\Conversation;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateMessageRequest;
use App\Http\Requests\showMessagesRequest;
use App\Services\MessageService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

class MessageController extends Controller
{
    use ApiSuccessTrait;
    public function __construct(
        protected MessageService $messageService,
    )
    {}

    public function showMessages(showMessagesRequest $request , int $conversationId): JsonResponse
    {
        $data = $this->messageService->fetchThreadMessagesOfConv($conversationId , $request->before_id);

        return $this->dataResponse($data , 200);
    }

    public function send(int $convId , CreateMessageRequest $request): JsonResponse
    {
        $data = $this->messageService->send($convId , $request->validated());

        return response()->json([
            'message'          => 'تم إرسال الرسالة',
            'message_id'       => $data['message_id'],
            'bot_replied'      => $data['bot_replied'],
            'bot_message_id'   => $data['bot_message_id'],
        ], 201);
    }
}
