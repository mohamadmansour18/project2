<?php

namespace App\Services;

use App\Enums\GroupMemberRole;
use App\Enums\JoinRequestStatus;
use App\Helpers\UrlHelper;
use App\Models\User;
use App\Repositories\JoinRequestRepository;
use App\Repositories\GroupMemberRepository;
use App\Repositories\GroupRepository;
use App\Exceptions\PermissionDeniedException;

class JoinRequestService
{
    public function __construct(
        protected JoinRequestRepository $requestRepo,
        protected GroupRepository $groupRepo,
        protected GroupMemberRepository $memberRepo,
        protected FcmNotificationDispatcherService $dispatcherService
    ) {}

    public function send(int $groupId, User $user): void
    {
        if ($this->memberRepo->isInAnyGroup($user->id)) {
            throw new PermissionDeniedException('طلب مرفوض', 'أنت بالفعل عضو في مجموعة.', 403);
        }

        if ($this->requestRepo->hasPendingRequest($groupId, $user->id)) {
            throw new PermissionDeniedException('طلب موجود', 'لديك طلب انضمام معلق لهذه المجموعة.', 403);
        }

        $memberCount = $this->groupRepo->getMemberCount($groupId);

        if ($memberCount >= 5) {
            throw new PermissionDeniedException('المجموعة ممتلئة', 'لا يمكن الانضمام، عدد الأعضاء مكتمل.', 403);
        }

        $this->requestRepo->create($groupId, $user->id);

        // إشعار قائد المجموعة
        $leader = $this->memberRepo->getLeader($groupId);
        if ($leader) {
            $group = $this->groupRepo->getById($groupId);
            $title = 'طلب انضمام جديد';
            $body = "{$user->name} أرسل طلب انضمام إلى مجموعتك {$group->name}";
            $this->dispatcherService->sendToUser($leader, $title, $body);
        }
    }

    public function getPendingForGroup(int $groupId): array
    {
        $requests = $this->requestRepo->getPendingForGroupWithUserAndProfile($groupId);

        return $requests->map(function ($request) {
            $profile = optional($request->user->profile);
            $profileImagePath = $profile->profile_image;
            return [
                'id' => $request->id,
                'group_id' => $request->group_id,
                'user_id' => $request->user_id,
                'status' => $request->status->value,
                'user' => [
                    'name' => $request->user->name,
                    'student_speciality' => $profile->student_speciality?->value,
                    'profile_image' => UrlHelper::imageUrl($profileImagePath)

                ],
            ];
        })->toArray();
    }

    public function getUserPendingRequests(User $user): array
    {
        $requests = $this->requestRepo->getUserPendingRequestsWithGroup($user->id);

        return $requests->map(function ($request) {
            return [
                'id' => $request->id,
                'group_id' => $request->group_id,
                'status' => $request->status->value,
                'group' => [
                    'id' => $request->group->id,
                    'name' => $request->group->name,
                    'speciality_needed' => $request->group->speciality_needed,
                    'image' => UrlHelper::imageUrl($request->group->image)
                ],
            ];
        })->toArray();
    }

    public function accept(int $requestId, User $leader): void
    {
        $request = $this->requestRepo->findPendingById($requestId);

        if ($request->group->number_of_members >= 5) {
            throw new PermissionDeniedException('ممتلئة', 'لا يمكن قبول الطلب، المجموعة مكتملة', 403);
        }

        // add student as a member
        $this->memberRepo->create($request->group_id, $request->user_id, GroupMemberRole::Member);
        $request->group->increment('number_of_members');

        $this->requestRepo->updateStatus($request, JoinRequestStatus::Accepted);

        // إشعار الطالب
        $student = $request->user;
        $group = $request->group;
        $title = 'تم قبول طلبك';
        $body = "تمت الموافقة على انضمامك إلى مجموعة {$group->name}";
        $this->dispatcherService->sendToUser($student, $title, $body);
    }

    public function reject(int $requestId, User $leader): void
    {
        $request = $this->requestRepo->findPendingById($requestId);

        $this->requestRepo->updateStatus($request, JoinRequestStatus::Rejected);

        // إشعار الطالب
        $student = $request->user;
        $group = $request->group;
        $title = 'تم رفض طلبك';
        $body = "تم رفض طلب انضمامك إلى مجموعة {$group->name}";
        $this->dispatcherService->sendToUser($student, $title, $body);
    }

    public function cancel(int $requestId, User $student): void
    {
        $request = $this->requestRepo->findPendingById($requestId);

        if (!$request) {
            throw new PermissionDeniedException('غير موجود', 'الطلب غير موجود أو تم التعامل معه', 404);
        }

        if ($request->user_id !== $student->id) {
            throw new PermissionDeniedException('مرفوض', 'فقط الطالب صاحب الطلب يمكنه إلغاءه', 403);
        }

        $this->requestRepo->updateStatus($request, JoinRequestStatus::Cancelled);
    }



}
