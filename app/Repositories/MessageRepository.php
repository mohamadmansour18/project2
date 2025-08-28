<?php

namespace App\Repositories;

use App\Enums\ConversationType;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Exceptions\ConversationException;
use App\Models\Conversation;
use App\Models\FAQ;
use App\Models\Message;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

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

    ///////////////////////////////////////////////////////
    public function storeMessage(Conversation $conv , int $senderId , array $payload)
    {
        return DB::transaction(function () use ($conv , $senderId, $payload){
            $attachmentPath = null;

            //store the attachment if exists
            if(!empty($payload['file']) && $payload['file'] instanceof UploadedFile)
            {
                //store local
                $dir = match ($payload['message_type']) {
                    MessageType::Image->value => 'chat/images',
                    MessageType::File->value => 'chat/files',
                    default => 'chat/other',
                };
                $attachmentPath = $payload['file']->store($dir , 'public');
            }

            $message = Message::create([
                'conversation_id' => $conv->id,
                'sender_id' => $senderId,
                'faq_id' => null,
                'message_type' => $payload['message_type'],
                'content' => $payload['message_type'] === MessageType::Text->value ? ($payload['content'] ?? null) : null,
                'attachment_path' => $attachmentPath,
                'status' => MessageStatus::Sent->value,
            ]);

            $conv->update(['last_message_at' => now()]);

            return $message->fresh();
        });
    }

    public function storeBotReply(Conversation $conv, FAQ $faq): Message
    {
        return DB::transaction(function () use ($conv, $faq) {
            $message = Message::create([
                'conversation_id' => $conv->id,
                'sender_id'       => null,
                'faq_id'          => $faq->id,
                'message_type'    => MessageType::Text->value,
                'content'         => $faq->answer,
                'attachment_path' => null,
                'status'          => MessageStatus::Sent->value,
            ]);

            $conv->update(['last_message_at' => now()]);

            return $message->fresh();
        });
    }
}
