<?php

namespace App\Services;

use App\Enums\ConversationType;
use App\Enums\MessageType;
use App\Enums\UserRole;
use App\Events\BotRepliedEvent;
use App\Events\MessageCreatedEvent;
use App\Helpers\UrlHelper;
use App\Models\FAQ;
use App\Models\User;
use App\Repositories\ConversationRepository;
use App\Repositories\MessageRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

class MessageService
{
    public function __construct(
        protected MessageRepository $messageRepository ,
        protected ConversationRepository $conversationRepository,
        protected FaqMatcher $faqMatcher,
        protected FcmNotificationDispatcherService $dispatcherService
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

    /////////////////////////////////////////////////////////////////////

    /**
     * @return array{message_id:int, bot_replied:bool, bot_message_id:int|null}
     */
    public function send(int $conversationId , array $payload): array
    {
        $senderId = Auth::id();

        [$conv , $peer] = $this->messageRepository->getConversationForUserOrFail($conversationId , $senderId);

        $stored = $this->messageRepository->storeMessage($conv , $senderId , $payload);

        if($conv->type === ConversationType::Self)
        {
            return [
                'message_id'     => $stored->id,
                'bot_replied'    => false,
                'bot_message_id' => null,
            ];
        }

        broadcast(new MessageCreatedEvent(
            conversationId : $conv->id,
            payload : [
                'id'         => $stored->id,
                'sender_id'  => $stored->sender_id,
                'faq_id'     => $stored->faq_id,
                'type'       => $stored->message_type,
                'content'    => $stored->message_type === MessageType::Text ? ($stored->content ?? null) : null,
                'status'     => $stored->status,
                'messageTime' => $this->displayTimeOrDate($stored->created_at),
            ]
        ))->toOthers();

        $botRepliedId = null;
        $senderRole = User::findOrFail($senderId);

        if($conv->type === ConversationType::Student_Doctor && $payload['message_type'] === MessageType::Text->value &&  $senderRole->role === UserRole::Student)
        {
            $match = $this->faqMatcher->match($payload['content'] ?? '');

            if(!empty($match) && $match['faq'] instanceof FAQ)
            {
                $botMsg = $this->messageRepository->storeBotReply($conv , $match['faq']);

                broadcast(new BotRepliedEvent(
                    conversationId : $conv->id,
                    payload : [
                        'id'            => $botMsg->id,
                        'sender_id'     => null,
                        'faq_id'        => $match['faq']->id,
                        'type'          => MessageType::Text->value,
                        'content'       => $match['faq']->answer,
                        'attachment_path' => null,
                        'status'        => $botMsg->status,
                        'messageTime' => $this->displayTimeOrDate($botMsg->created_at)
                    ]
                ));
                $botRepliedId = $botMsg->id;
            } else {
                $this->dispatcherService->sendToUser(
                    $peer,
                    title: '! رسالة جديدة',
                    body:  "لديك رسالة جديدة في محادثاتك من : $peer->name"
                );
            }
        } else {

            if ($peer && $peer->id !== $senderId) {
                $this->dispatcherService->sendToUser(
                    $peer,
                    title: '! رسالة جديدة',
                    body:   "لديك رسالة جديدة في محادثاتك من : $peer->name"
                );
            }
        }

        return [
            'message_id'     => $stored->id,
            'bot_replied'    => (bool) $botRepliedId,
            'bot_message_id' => $botRepliedId,
        ];
    }
}
