<?php

namespace App\Services;

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
}
