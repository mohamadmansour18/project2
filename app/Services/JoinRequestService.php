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
            throw new PermissionDeniedException('! غير مسموح', 'لا يمكنك الانضمام بعد الموافقة على الاستمارة الأولى.',403);
        }

//        if (!$this->formPeriodRepo->isFormPeriodActive(FormSubmissionPeriodFormName::Form1->value)) {
//            throw new PermissionDeniedException('! غير مسموح', 'انتهت فترة الانضمام، لم يعد ممكناً الانضمام للمجموعة.',403);
//        }

        if ($this->memberRepo->isInAnyGroup($user->id)) {
            throw new PermissionDeniedException('! طلب مرفوض', 'أنت بالفعل عضو في مجموعة.', 403);
        }

        if ($this->requestRepo->hasPendingRequest($groupId, $user->id)) {
            throw new PermissionDeniedException('! طلب موجود', 'لديك طلب انضمام معلق لهذه المجموعة.', 403);
        }

        $memberCount = $this->groupRepo->getMemberCount($groupId);

        if ($memberCount >= 5) {
            throw new PermissionDeniedException('! المجموعة ممتلئة', 'لا يمكن الانضمام، عدد الأعضاء مكتمل.', 403);
        }

        $this->requestRepo->create($groupId, $user->id);

        // إشعار قائد المجموعة
        $leader = $this->memberRepo->getLeader($groupId);
        if ($leader) {
            $group = $this->groupRepo->getById($groupId);
            $title = '! طلب انضمام جديد';
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
                'description' => $request->description,
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

    public function acceptUnified(int $requestId, User $leader): void
    {
        $request = $this->requestRepo->findByIdWithGroup($requestId);
        $memberCount = $this->groupRepo->getMemberCount($request->group_id);

        // ---------------------- العضو العادي ----------------------
        if ($memberCount < 5) {
            if ($request->status !== JoinRequestStatus::Pending || $request->description !== null) {
                throw new PermissionDeniedException('! غير صالح', 'هذا الطلب ليس طلب انضمام عادي.', 403);
            }
            if ($request->status !== JoinRequestStatus::Pending) {
                throw new PermissionDeniedException('! تم التعامل', 'تم التعامل مع الطلب سابقا', 403);
            }

            $this->memberRepo->create($request->group_id, $request->user_id, GroupMemberRole::Member);
            $request->group->increment('number_of_members');

            $this->requestRepo->updateStatus($request, JoinRequestStatus::Accepted);

            // إشعار الطالب
            $this->dispatcherService->sendToUser(
                $request->user,
                '! تم قبول طلبك',
                "تمت الموافقة على انضمامك إلى مجموعة {$request->group->name}"
            );

            return;
        }

        // ---------------------- الطالب السادس ----------------------
        if ($memberCount == 5) {
            if ($request->status !== JoinRequestStatus::PendingLeader || empty($request->description)) {
                throw new PermissionDeniedException('! غير صالح', 'هذا الطلب ليس مخصصاً للطالب السادس قم بتقديم طلب انضمام طالب سادس.', 403);
            }

            if ($request->status !== JoinRequestStatus::PendingLeader) {
                throw new PermissionDeniedException('! تم التعامل', 'تم التعامل مع الطلب سابقا', 403);
            }
            // هنا الليدر يوافق → نحول الطلب لرئيس القسم
            $this->requestRepo->updateStatus($request, JoinRequestStatus::PendingHead);

            $this->dispatcherService->sendToUser(
                $request->user,
                '! طلبك قيد المراجعة',
                "تمت موافقة الليدر على طلبك، وتم تحويله لرئيس القسم."
            );

            return;
        }

        // ---------------------- مرفوض ----------------------
        throw new PermissionDeniedException('! ممتلئة', 'المجموعة مكتملة بالكامل', 403);
    }

    public function rejectUnified(int $requestId, User $leader): void
    {

        $request = $this->requestRepo->findByIdWithGroup($requestId);

        if ($request->status !== JoinRequestStatus::PendingLeader && $request->status !== JoinRequestStatus::Pending) {
            throw new PermissionDeniedException('! تم التعامل', 'تم التعامل مع الطلب سابقا', 403);
        }

        // فقط الحالات المسموحة للرفض
        if (in_array($request->status, [
            JoinRequestStatus::Pending,
            JoinRequestStatus::PendingLeader,
        ])) {
            $this->requestRepo->updateStatus($request, JoinRequestStatus::Rejected);

            // تحديد مين اللي رفض
            $whoRejected = match ($request->status) {
                JoinRequestStatus::Pending       => "الليدر",
                JoinRequestStatus::PendingLeader => "الليدر",
                default                          => "غير معروف"
            };

            // إرسال إشعار للطالب
            $this->dispatcherService->sendToUser(
                $request->user,
                '! تم رفض طلبك',
                "$whoRejected رفض طلبك للانضمام إلى مجموعة {$request->group->name}"
            );

            return;
        }

        throw new PermissionDeniedException('! غير صالح', 'لا يمكن رفض هذا الطلب.', 403);
    }



    public function cancelByGroup(int $groupId, User $student): void
    {
        $request = $this->requestRepo->findPendingByGroupAndUser($groupId, $student->id);

        if (!$request) {
            throw new PermissionDeniedException('! غير موجود', 'الطلب غير موجود أو تم التعامل معه', 404);
        }

        $this->requestRepo->updateStatus($request, JoinRequestStatus::Cancelled);
    }

    public function sendSixthMemberRequest(int $groupId, User $user, ?string $description = null): void
    {
        if ($this->projectForm1Repo->isApprovedForGroup($groupId)) {
            throw new PermissionDeniedException('! غير مسموح', 'لا يمكنك الانضمام بعد الموافقة على الاستمارة الأولى.');
        }

        if (!$this->formPeriodRepo->isFormPeriodActive(FormSubmissionPeriodFormName::Form1->value)) {
            throw new PermissionDeniedException('! غير مسموح', 'انتهت فترة الانضمام، لم يعد ممكناً الانضمام للمجموعة.');
        }

        // تحقق: الطالب مو داخل أي مجموعة
        if ($this->memberRepo->isInAnyGroup($user->id)) {
            throw new PermissionDeniedException('! طلب مرفوض', 'أنت بالفعل عضو في مجموعة.', 403);
        }

        // تحقق: ما عنده طلب سابق
        if ($this->requestRepo->hasPendingRequestSixthStudent($groupId, $user->id)) {
            throw new PermissionDeniedException('! طلب موجود', 'لديك طلب انضمام معلق لهذه المجموعة.', 403);
        }

        // تحقق: المجموعة فيها 5 أعضاء فقط (الطالب السادس)
        $memberCount = $this->groupRepo->getMemberCount($groupId);
        if ($memberCount < 5) {
            throw new PermissionDeniedException('! غير مؤهل', 'هذا الطلب مخصص فقط للانضمام كمشارك سادس.', 403);
        }
        if ($memberCount >= 6) {
            throw new PermissionDeniedException('! ممتلئة', 'المجموعة مكتملة بالكامل ولا يمكن الانضمام.', 403);
        }

        // إنشاء الطلب
        $this->requestRepo->createSixthMemberRequest($groupId, $user->id, $description);

        // إشعار القائد
        $leader = $this->memberRepo->getLeader($groupId);
        if ($leader) {
            $group = $this->groupRepo->getById($groupId);
            $title = '! طلب انضمام جديد (طالب سادس)';
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
            throw new PermissionDeniedException('! غير موجود', 'الطلب غير موجود أو تم التعامل معه', 404);
        }

        // إضافة الطالب للمجموعة
        $this->memberRepo->create($request->group_id, $request->user_id, GroupMemberRole::Member);
        $request->group->increment('number_of_members');

        $this->requestRepo->updateStatus($request, JoinRequestStatus::Accepted);

        // إشعار الطالب
        $title = '! تم قبول طلبك';
        $body = "تمت الموافقة على انضمامك إلى مجموعة {$request->group->name}";
        $this->dispatcherService->sendToUser($request->user, $title, $body);
    }

    public function headReject(int $requestId): void
    {
        $request = $this->requestRepo->findPendingByIdSixthHead($requestId);

        if (!$request || $request->status !== JoinRequestStatus::PendingHead) {
            throw new PermissionDeniedException('! غير موجود', 'الطلب غير موجود أو تم التعامل معه', 404);
        }

        $this->requestRepo->updateStatus($request, JoinRequestStatus::Rejected);

        // إشعار الطالب
        $title = '! تم رفض طلبك';
        $body = "رفض رئيس القسم طلبك للانضمام إلى {$request->group->name}";
        $this->dispatcherService->sendToUser($request->user, $title, $body);
    }



}
