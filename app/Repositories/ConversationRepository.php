<?php

namespace App\Repositories;

use App\Enums\ConversationType;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Models\Conversation;
use Carbon\Carbon;

class ConversationRepository
{
    public function createConversation(array $data): Conversation
    {
        return Conversation::create($data);
    }

    public function getUserConversations(int $userId): array
    {
        $conversations = Conversation::query()
                            ->where(function ($query) use ($userId) {
                                $query->where('user_one_id' , $userId)
                                      ->orWhere('user_two_id' , $userId);
                            })
                            ->with(['firstUser.Profile' , 'secondUser.Profile'])
                            ->withCount(['messages as unread_count' => function ($query) use ($userId){
                                $query->where('sender_id' , '!=' , $userId)
                                      ->where('status' , '!=' , MessageStatus::Read);
                            }])
                            ->get()
                            ->map(function ($conv) use ($userId){
                                $lastMessage = $conv->messages()->latest()->first();

                                $peer = $conv->type === ConversationType::Self ?
                                    [
                                        'id' => $userId,
                                        'name' => $conv->firstUser->name,
                                        'profile_image' => optional($conv->firstUser->profile)->profile_image,
                                        'role' => $conv->firstUser->role,
                                    ] :
                                    $this->getPeerData($conv , $userId);

                                return [
                                    'conversation_id' => $conv->id,
                                    'title' => $conv->type === ConversationType::Self ? 'الرسائل المحفوظة' : null ,
                                    'conversation_type' => $conv->type,
                                    'peer' => $peer,
                                    'last_message' => $lastMessage?->sender_id != $userId ? $this->formatMessage($lastMessage) : 'أنت : '.$this->formatMessage($lastMessage),
                                    'last_message_at' => $this->formatLastMessageAt($lastMessage?->created_at),
                                    'unread_count' => $conv->unread_count ?? 0,
                                    'is_self' => $conv->type === ConversationType::Self,
                                ];
                            })
                            ->sortByDesc('last_message_at')
                            ->values()
                            ->toArray();

        usort($conversations , function ($a, $b) {
            if($a['is_self'])
                return -1;
            if($b['is_self'])
                return 1;
            return 0;
        });

        return $conversations ;
    }

    private function getPeerData($conv , $userId): array
    {
        $peer = $conv->user_one_id == $userId ? $conv->secondUser : $conv->firstUser;

        return [
            'id' => $peer->id ,
            'name' => $peer->name ,
            'profile_image' => optional($peer->profile)->profile_image,
            'role' => $peer->role,
        ];
    }

    private function formatMessage($message): ?string
    {
        if(!$message)
        {
            return null;
        }
        if($message->message_type === MessageType::Image)
        {
            return '🖼️ صورة';
        }
        if($message->message_type === MessageType::File)
        {
            return '📎 ملف';
        }

        $text = $message->content;

        return mb_strlen($text) > 40 ? mb_substr($text , 0 , 40) . '...' : $text ;
    }

    private function formatLastMessageAt($datetime): ?string
    {
        if(!$datetime)
        {
            return null;
        }

        $date = Carbon::parse($datetime);

        if($date->isToday()){
            return $date->format('g:i A');
        }

        return $date->format('d/m/Y');
    }
}
