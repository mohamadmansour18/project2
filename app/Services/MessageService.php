<?php

namespace App\Services;

use App\Helpers\UrlHelper;
use App\Repositories\MessageRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

class MessageService
{
    public function __construct(
        protected MessageRepository $messageRepository
    )
    {}

    public function fetchThreadMessagesOfConv(int $conversationId , ?int $beforeId): array
    {
        $myId = Auth::id();

        //fetch the details of conv and the second user info
        [$conv , $peer] = $this->messageRepository->getConversationForUserOrFail($conversationId , $myId);

        //fetch messages using $BeforeId
        [$messages , $nextBeforeId] = $this->messageRepository->getLastMessages($conversationId , $beforeId , 30);

        //make the messages of second user as read
        $updated = $this->messageRepository->markOthersAsRead($conversationId , $myId);

        //fetch data
        return [
            'conversation_details' => [
                'conversation_id' => $conv->id,
                'conversation_type' => $conv->type,
                'peer' => [
                    'id'            => $peer->id,
                    'name'          => $peer->name,
                    'profile_image' => UrlHelper::imageUrl(optional($peer->profile)->profile_image),
                ]
            ],
            'messages' => array_map(function ($m) use ($myId){
                return [
                    'id' => $m->id,
                    'sender_id' => $m->sender_id,
                    'faq_id' => $m->faq_id,
                    'message_type' => $m->message_type,
                    'content' => $m->content,
                    'attachment_path' => $m->attachment_path,
                    'is_mine' => $m->sender_id === $myId,
                    'messageTime' => $this->displayTimeOrDate($m->created_at)
                ];
            } , $messages),
            'next_before_id' => $nextBeforeId
        ];
    }

    private function displayTimeOrDate($carbon): string
    {
        if ($carbon->isToday()) {
            $time = $carbon->format('g:i A');
            return str_replace([' AM',' PM'], [' ص',' م'], $time);
        }

        return $carbon->format('d/m/Y');
    }
}
