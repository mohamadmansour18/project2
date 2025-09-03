<?php

namespace App\Services;

use App\Enums\ConversationType;
use App\Enums\UserRole;
use App\Exceptions\ConversationException;
use App\Models\Conversation;
use App\Repositories\ConversationRepository;
use Illuminate\Support\Facades\Auth;

class ConversationService
{
    public function __construct(
        protected ConversationRepository $conversationRepository
    )
    {}

    public function listUserConversations(): array
    {
        $userId = Auth::id();

        return $this->conversationRepository->getUserConversations($userId);
    }

    public function listUserOptionForConversations(): array
    {
        $userId = Auth::id();

        return $this->conversationRepository->getStartConversationData($userId);
    }

    public function listUserOptionForConversationsDoctor(): array
    {
        $userId = Auth::id();

        return $this->conversationRepository->getStartConversationDataDoctor($userId);
    }
    public function searchConversation(string $keyword): array
    {
        $myID = Auth::id();

        return $this->conversationRepository->searchUserConversation($myID , $keyword);
    }

    public function createConversationBetweenUsers(int $otherUserId): void
    {
        $myId = Auth::id();

        if($myId === $otherUserId)
        {
            throw new ConversationException('! لايمكن انشاء المحادثة' , 'لايمكنك فتح محادثة مع نفسك من هذا التاب' , 422);
        }

        [$a , $b] = $this->conversationRepository->getUserOrFail($myId , $otherUserId);

        $type = $this->detectType($a->role->value , $b->role->value);

        $userOneId = min($a->id , $b->id);
        $userTwoId = max($a->id , $b->id);

        $exists = $this->conversationRepository->exists($type , $userOneId , $userTwoId);

        if($exists)
        {
            throw new ConversationException('! لايمكن انشاء هذه المحادثة' , 'هذه المحادثة موجودة لديك مسبقا لايمكنك انشائها مرة اخرى' , 422);
        }

        $this->conversationRepository->createConversation([
            'type' => $type,
            'user_one_id' => $userOneId,
            'user_two_id' => $userTwoId,
            'last_message_at' => null
        ]);
    }

    /////////////////////////////////////////////////////////////

    private function detectType(string $roleA , string $roleB): ConversationType
    {
        $roles = collect([$roleA , $roleB]);

        $hasDoctor = $roles->contains(UserRole::Doctor->value);
        $hasStudent = $roles->contains(UserRole::Student->value);

        if($hasDoctor && $hasStudent)
        {
            return ConversationType::Student_Doctor;
        }

        return ConversationType::Student_Student;
    }
}
