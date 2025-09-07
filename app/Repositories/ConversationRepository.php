<?php

namespace App\Repositories;

use App\Enums\ConversationType;
use App\Enums\GroupMemberRole;
use App\Enums\MessageStatus;
use App\Enums\MessageType;
use App\Enums\UserRole;
use App\Exceptions\ConversationException;
use App\Helpers\UrlHelper;
use App\Models\Conversation;
use App\Models\GroupMember;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use function PHPSTORM_META\map;

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
                            ->orderByRaw('CASE WHEN type = ? THEN 0 ELSE 1 END', [ConversationType::Self])
                            ->orderByDesc('last_message_at')
                            ->get()
                            ->map(function ($conv) use ($userId){
                                $lastMessage = $conv->messages()->latest()->first();

                                $peer = $conv->type === ConversationType::Self ?
                                    [
                                        'id' => $userId,
                                        'name' => "الرسائل المحفوظة",
                                        'profile_image' => UrlHelper::imageUrl(optional($conv->firstUser->profile)->profile_image),
                                        'role' => $conv->firstUser->role,
                                    ] :
                                    $this->getPeerData($conv , $userId);

                                return [
                                    'conversation_id' => $conv->id,
                                    'title' => $conv->type === ConversationType::Self ? 'الرسائل المحفوظة' : null ,
                                    'conversation_type' => $conv->type,
                                    'peer' => $peer,
                                    'last_message' => $lastMessage?->sender_id != $userId ? $this->formatMessage($lastMessage) : 'أنت : '.$this->formatMessage($lastMessage),
                                    'last_message_at' => $this->formatLastMessageAt($conv->last_message_at),
                                    'unread_count' => $conv->unread_count ?? 0,
                                    'is_self' => $conv->type === ConversationType::Self,
                                ];
                            })
                            ->values()
                            ->toArray();


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

        if ($date->isToday()) {
            $time = $date->format('g:i A');
            return str_replace([' AM',' PM'],[' ص',' م'],$time);
        }

        if ($date->isYesterday()) {
            return 'أمس';
        }

        return $date->format('d/m/Y');
    }

    /////////////////////////////////////////////////////////////

    public function getStartConversationData(int $userId): array
    {
        //get all doctor in system
        $doctors = User::query()->where('role' , UserRole::Doctor->value)
                                ->with(['profile:id,user_id,profile_image'])
                                ->orderBy('name')
                                ->get(['id' , 'name'])
                                ->map(function($doctor){
                                    return [
                                        'id' => $doctor->id,
                                        'name' => $doctor->name,
                                        'profile_image' => UrlHelper::imageUrl(optional($doctor->profile)->profile_image),
                                    ];
                                })
                                ->values();


        //get group members without me
        $myGroupMembers = $this->getMyGroupMembers($userId)
            ->map(function($member){
                return [
                    'id' => $member->id,
                    'name' => $member->name,
                    'profile_image' => UrlHelper::imageUrl(optional($member->profile)->profile_image),
                ];
            })
            ->values();


        //get all student for current year without my group member
        $currentYear = Carbon::now()->year;
        $excludedIds = $myGroupMembers->pluck('id')->push($userId);

        $students = User::query()->where('role' , UserRole::Student->value)
                                ->whereYear('created_at' , $currentYear)
                                ->whereNotIn('id' , $excludedIds)
                                ->with(['profile:id,user_id,profile_image'])
                                ->orderBy('name')
                                ->get(['id' , 'name'])
                                ->map(function($student){
                                    return [
                                        'id' => $student->id,
                                        'name' => $student->name,
                                        'profile_image' => UrlHelper::imageUrl(optional($student->profile)->profile_image),
                                    ];
                                })
                                ->values();

        return [
            'doctors' => $doctors,
            'myGroup' => $myGroupMembers,
            'students' => $students
        ];
    }

    public function getStartConversationDataDoctor(int $userId): array
    {
        //get all student in system
        $students = User::query()->where('role' , UserRole::Student->value)
            ->whereYear('created_at' , now()->year)
            ->with(['profile:id,user_id,profile_image'])
            ->get(['id' , 'name'])
            ->map(function($doctor){
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->name,
                    'profile_image' => UrlHelper::imageUrl(optional($doctor->profile)->profile_image),
                ];
            })
            ->values();

        $groupAdmins = GroupMember::query()
            ->where('role' , GroupMemberRole::Leader->value)
            ->whereYear('created_at' , now()->year)
            ->with('user.profile:id,user_id,profile_image')
            ->get()
            ->map(function($groupAdmin){
                return [
                    'id' => $groupAdmin?->user?->id,
                    'name' => $groupAdmin?->user?->name,
                    'profile_image' => UrlHelper::imageUrl(optional($groupAdmin->user->profile)?->profile_image),
                ];
            });

        return [
            'all' => $students,
            'adminGroups' => $groupAdmins,
        ];
    }

    private function getMyGroupMembers(int $userId)
    {
        $groupId = DB::table('group_members')->where('user_id' , $userId)->value('group_id');

        if(!$groupId)
        {
            return collect();
        }

        return User::whereHas('groupMember' , function ($query) use ($groupId){
            $query->where('group_id' , $groupId);
        })
        ->where('id' , '!=' , $userId)
        ->with(['profile:id,user_id,profile_image'])
        ->get(['id' , 'name']);
    }

    /////////////////////////////////////////////////////////////

    public function getUserOrFail(int $userA , int $userB): array
    {
        $a = User::query()->find($userA);
        $b = User::query()->find($userB);

        if(!$a || !$b)
        {
            throw new ConversationException('لايمكن انشاء هذه المحادثة !' , 'أحد طرفي المحادثة غير موجود' , 404);
        }

        return [$a, $b];
    }

    public function exists(ConversationType  $type , int $userOneId , int $userTwoId): bool
    {
        return Conversation::query()
                           ->where('type' , $type->value)
                           ->where('user_one_id' , $userOneId)
                           ->where('user_two_id' , $userTwoId)
                           ->exists();
    }

    /////////////////////////////////////////////////////////////

    public function searchUserConversation(int $userId , string $keyword)
    {
        $keyword = trim($keyword);

        $conversations = Conversation::query()
                                     ->where(function ($query) use ($userId){
                                         $query->where('user_one_id' , $userId)
                                               ->orWhere('user_two_id' , $userId);
                                     })
                                     ->with(['firstUser.profile' , 'secondUser.profile'])
                                     ->withCount(['messages as unread_count' => function($query) use ($userId){
                                         $query->where('sender_id' , '!=' , $userId)
                                               ->where('status' , '!=' , MessageStatus::Read);
                                     }])
                                     ->get()
                                     ->filter(function($conv) use ($userId , $keyword){
                                         if($conv->type === ConversationType::Self && stripos('الرسائل المحفوظة', $keyword) === 0)
                                         {
                                             return true ;
                                         }

                                         $peer = $conv->user_one_id == $userId ? $conv->secondUser : $conv->firstUser ;
                                         return stripos($peer->name , $keyword) === 0 ;
                                     })
                                     ->map(function($conv) use ($userId) {
                                         $lastMessage = $conv->messages()->latest()->first();

                                         $peer = $conv->type === ConversationType::Self ?
                                             [
                                                 'id' => $userId,
                                                 'name' => $conv->firstUser->name,
                                                 'profile_image' => UrlHelper::imageUrl(optional($conv->firstUser->profile)->profile_image),
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
                                     ->values()
                                     ->toArray();

        return $conversations;

    }
}
