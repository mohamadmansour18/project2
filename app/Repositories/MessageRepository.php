<?php

namespace App\Repositories;

use App\Enums\ConversationType;
use App\Enums\MessageStatus;
use App\Exceptions\ConversationException;
use App\Models\Conversation;
use App\Models\Message;

class MessageRepository
{
    public function getConversationForUserOrFail(int $conversationId, int $userId): array
    {
        $conv = Conversation::query()->with(['firstUser.profile', 'secondUser.profile'])->find($conversationId);

        if(!$conv || !in_array($userId , [$conv->user_one_id , $conv->user_two_id]))
        {
            throw new ConversationException('لايمكن عرض محتوى المحادثة !' , 'هذه المحادثة غير موجودة اساسا او انت لست طرفا فيها' , 404);
        }

        $peer = $conv->user_one_id === $userId ? $conv->secondUser : $conv->firstUser ;

        if($conv->type === ConversationType::Self)
        {
            $peer = $conv->firstUser;
        }

        return [$conv , $peer];
    }

    public function getLastMessages(int $conversationId , ?int $beforeId , int $limit): array
    {
        //sort all messages for specific conversation Desc :[5 , 4 , 3 , 2 , 1] "ترتيب حسب احدث رسالة"
        $base = Message::query()
                       ->where('conversation_id', $conversationId)
                       ->orderByDesc('id');

        if($beforeId)
        {
            $base->where('id' , '<' , $beforeId);
        }

        //limit result to 3 for example : [5 , 4 , 3]  and reversing the result : [3 , 4 , 5] "لجلب الأيدي الخاص باقدم رسالة لاستخدامه في التقطيعة القدامة"
        $rows = $base->limit($limit)->get()->reverse()->values();

        $nextBeforeId = $rows->isNotEmpty() ? $rows->first()->id : null;

        return [$rows->all() , $nextBeforeId];
    }

    public function markOthersAsRead(int $conversationId, int $userId): array
    {
        $toRead = Message::query()
            ->where('conversation_id', $conversationId)
            ->whereNotNull('sender_id')
            ->where('sender_id' , '!=' , $userId)
            ->where('status' , '!=' , MessageStatus::Read->value)
            ->pluck('id');

        if($toRead->isEmpty())
        {
            return['count' => 0 , 'max_id' => null];
        }

        Message::query()
            ->whereIn('id' , $toRead)
            ->update([
                'status' => MessageStatus::Read->value,
                'updated_at' => now()
            ]);

        return ['count' => $toRead->count() , 'max_id' => $toRead->max()];
    }
}
