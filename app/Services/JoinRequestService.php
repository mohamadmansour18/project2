<?php

namespace App\Services;

use App\Enums\FormSubmissionPeriodFormName;
use App\Enums\GroupMemberRole;
use App\Enums\JoinRequestStatus;
use App\Helpers\UrlHelper;
use App\Models\User;
use App\Repositories\FormSubmissionPeriodRepository;
use App\Repositories\JoinRequestRepository;
use App\Repositories\GroupMemberRepository;
use App\Repositories\GroupRepository;
use App\Exceptions\PermissionDeniedException;
use App\Repositories\ProjectFormRepository;

class JoinRequestService
{
    public function __construct(
        protected JoinRequestRepository $requestRepo,
        protected GroupRepository $groupRepo,
        protected GroupMemberRepository $memberRepo,
        protected FcmNotificationDispatcherService $dispatcherService,
        protected ProjectFormRepository $projectForm1Repo,
        protected FormSubmissionPeriodRepository $formPeriodRepo
    ) {}

    public function send(int $groupId, User $user): void
    {
        if ($this->projectForm1Repo->isApprovedForGroup($groupId)) {
            throw new PermissionDeniedException('غير مسموح', 'لا يمكنك الانضمام بعد الموافقة على الاستمارة الأولى.',403);
        }

        if (!$this->formPeriodRepo->isFormPeriodActive(FormSubmissionPeriodFormName::Form1->value)) {
            throw new PermissionDeniedException('غير مسموح', 'انتهت فترة الانضمام، لم يعد ممكناً الانضمام للمجموعة.',403);
        }

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

    public function cancelByGroup(int $groupId, User $student): void
    {
        $request = $this->requestRepo->findPendingByGroupAndUser($groupId, $student->id);

        if (!$request) {
            throw new PermissionDeniedException('غير موجود', 'الطلب غير موجود أو تم التعامل معه', 404);
        }

        $this->requestRepo->updateStatus($request, JoinRequestStatus::Cancelled);
    }

    public function sendSixthMemberRequest(int $groupId, User $user, ?string $description = null): void
    {
        if ($this->projectForm1Repo->isApprovedForGroup($groupId)) {
            throw new PermissionDeniedException('غير مسموح', 'لا يمكنك الانضمام بعد الموافقة على الاستمارة الأولى.');
        }

        if (!$this->formPeriodRepo->isFormPeriodActive(FormSubmissionPeriodFormName::Form1->value)) {
            throw new PermissionDeniedException('غير مسموح', 'انتهت فترة الانضمام، لم يعد ممكناً الانضمام للمجموعة.');
        }

        // تحقق: الطالب مو داخل أي مجموعة
        if ($this->memberRepo->isInAnyGroup($user->id)) {
            throw new PermissionDeniedException('طلب مرفوض', 'أنت بالفعل عضو في مجموعة.', 403);
        }

        // تحقق: ما عنده طلب سابق
        if ($this->requestRepo->hasPendingRequestSixthStudent($groupId, $user->id)) {
            throw new PermissionDeniedException('طلب موجود', 'لديك طلب انضمام معلق لهذه المجموعة.', 403);
        }

        // تحقق: المجموعة فيها 5 أعضاء فقط (الطالب السادس)
        $memberCount = $this->groupRepo->getMemberCount($groupId);
        if ($memberCount < 5) {
            throw new PermissionDeniedException('غير مؤهل', 'هذا الطلب مخصص فقط للانضمام كمشارك سادس.', 403);
        }
        if ($memberCount >= 6) {
            throw new PermissionDeniedException('ممتلئة', 'المجموعة مكتملة بالكامل ولا يمكن الانضمام.', 403);
        }

        // إنشاء الطلب
        $this->requestRepo->createSixthMemberRequest($groupId, $user->id, $description);

        // إشعار القائد
        $leader = $this->memberRepo->getLeader($groupId);
        if ($leader) {
            $group = $this->groupRepo->getById($groupId);
            $title = 'طلب انضمام جديد (طالب سادس)';
            $body = "{$user->name} أرسل طلب انضمام كمشارك سادس إلى مجموعتك {$group->name}";
            $this->dispatcherService->sendToUser($leader, $title, $body);
        }
    }

    public function getLeaderPendingRequests(int $groupId): array
    {
        $requests = $this->requestRepo->getPendingForLeader($groupId);

        return $requests->map(fn($r) => [
            'id' => $r->id,
            'group_id' => $r->group_id,
            'user_id' => $r->user_id,
            'status' => $r->status->value,
            'description' => $r->description,
            'user' => [
                'name' => $r->user->name,
                'student_speciality' => $r->user->profile?->student_speciality?->value,
                'profile_image' => UrlHelper::imageUrl($r->user->profile?->profile_image)
            ]
        ])->toArray();
    }

    public function leaderApprove(int $requestId, int $leaderId): void
    {
        $request = $this->requestRepo->findPendingByIdSixth($requestId);

        if (!$request || $request->status !== JoinRequestStatus::PendingLeader) {
            throw new PermissionDeniedException('غير موجود', 'الطلب غير موجود أو تم التعامل معه', 404);
        }

        $this->requestRepo->updateStatus($request, JoinRequestStatus::PendingHead);
    }

    public function leaderReject(int $requestId, int $leaderId): void
    {
        $request = $this->requestRepo->findPendingByIdSixth($requestId);

        if (!$request || $request->status !== JoinRequestStatus::PendingLeader) {
            throw new PermissionDeniedException('غير موجود', 'الطلب غير موجود أو تم التعامل معه', 404);
        }
        $this->requestRepo->updateStatus($request, JoinRequestStatus::Rejected);

        // إشعار الطالب
        $title = 'تم رفض طلبك';
        $body = "رفض الليدر طلبك للانضمام إلى {$request->group->name}";
        $this->dispatcherService->sendToUser($request->user, $title, $body);
    }

    // طلبات رئيس القسم
    public function getHeadPendingRequests(): array
    {
        $requests = $this->requestRepo->getPendingForHead();
        return $requests->map(fn($r) => [
            'id' => $r->id,
            'group_id' => $r->group_id,
            'group_name'=>$r->group->name,
            'user_id' => $r->user_id,
            'status' => $r->status->value,
            'description' => $r->description,
            'user' => [
                'name' => $r->user->name,
                'student_speciality' => $r->user->profile?->student_speciality?->value,
                'profile_image' => UrlHelper::imageUrl($r->user->profile?->profile_image)
            ]
        ])->toArray();
    }

    public function headApprove(int $requestId): void
    {
        $request = $this->requestRepo->findPendingByIdSixthHead($requestId);

        if (!$request || $request->status !== JoinRequestStatus::PendingHead) {
            throw new PermissionDeniedException('غير موجود', 'الطلب غير موجود أو تم التعامل معه', 404);
        }

        // إضافة الطالب للمجموعة
        $this->memberRepo->create($request->group_id, $request->user_id, GroupMemberRole::Member);
        $request->group->increment('number_of_members');

        $this->requestRepo->updateStatus($request, JoinRequestStatus::Accepted);

        // إشعار الطالب
        $title = 'تم قبول طلبك';
        $body = "تمت الموافقة على انضمامك إلى مجموعة {$request->group->name}";
        $this->dispatcherService->sendToUser($request->user, $title, $body);
    }

    public function headReject(int $requestId): void
    {
        $request = $this->requestRepo->findPendingByIdSixthHead($requestId);

        if (!$request || $request->status !== JoinRequestStatus::PendingHead) {
            throw new PermissionDeniedException('غير موجود', 'الطلب غير موجود أو تم التعامل معه', 404);
        }

        $this->requestRepo->updateStatus($request, JoinRequestStatus::Rejected);

        // إشعار الطالب
        $title = 'تم رفض طلبك';
        $body = "رفض رئيس القسم طلبك للانضمام إلى {$request->group->name}";
        $this->dispatcherService->sendToUser($request->user, $title, $body);
    }



}
