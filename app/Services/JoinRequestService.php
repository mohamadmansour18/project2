<?php

namespace App\Services;

use App\Enums\GroupMemberRole;
use App\Enums\JoinRequestStatus;
use App\Models\User;
use App\Repositories\JoinRequestRepository;
use App\Repositories\GroupMemberRepository;
use App\Repositories\GroupRepository;
use App\Exceptions\PermissionDeniedException;
use Illuminate\Support\Facades\Auth;

class JoinRequestService
{
    public function __construct(
        protected JoinRequestRepository $requestRepo,
        protected GroupRepository $groupRepo,
        protected GroupMemberRepository $memberRepo,
        protected ImageService $imageService
    ) {}

    public function send(int $groupId, User $user, ?string $description = null): void
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

        $this->requestRepo->create($groupId, $user->id, $description);
    }

    public function getPendingForGroup(int $groupId): array
    {
        $user = Auth::user();

        // isLeader?
        if (!$this->memberRepo->isLeader($groupId, $user->id)) {
            throw new PermissionDeniedException('مرفوض', 'فقط القائد يمكنه رؤية الطلبات', 403);
        }

        $requests = $this->requestRepo->getPendingForGroupWithUserAndProfile($groupId);

        return $requests->map(function ($request) {
            $profile = optional($request->user->profile);
            $profileImagePath = $profile->profile_image;
            return [
                'id' => $request->id,
                'group_id' => $request->group_id,
                'user_id' => $request->user_id,
                'status' => $request->status->value,
                'description' => $request->description,
                'user' => [
                    'name' => $request->user->name,
                    'student_speciality' => $profile->student_speciality?->value,
                    'profile_image' => $this->imageService->getFullUrl($profileImagePath)

                ],
            ];
        })->toArray();
    }

    public function accept(int $requestId, User $leader): void
    {
        $request = $this->requestRepo->findPendingById($requestId);

        if (!$request) {
            throw new PermissionDeniedException('غير موجود', 'الطلب غير موجود أو تم التعامل معه', 404);
        }

        if (!$this->memberRepo->isLeader($request->group_id, $leader->id)) {
            throw new PermissionDeniedException('مرفوض', 'فقط القائد يمكنه قبول الطلب', 403);
        }

        if ($request->group->number_of_members >= 5) {
            throw new PermissionDeniedException('ممتلئة', 'لا يمكن قبول الطلب، المجموعة مكتملة', 403);
        }

        // add student as a member
        $this->memberRepo->create($request->group_id, $request->user_id, GroupMemberRole::Member);
        $request->group->increment('number_of_members');

        $this->requestRepo->updateStatus($request, JoinRequestStatus::Accepted);
    }

    public function reject(int $requestId, User $leader): void
    {
        $request = $this->requestRepo->findPendingById($requestId);

        if (!$request) {
            throw new PermissionDeniedException('غير موجود', 'الطلب غير موجود أو تم التعامل معه', 404);
        }

        if (!$this->memberRepo->isLeader($request->group_id, $leader->id)) {
            throw new PermissionDeniedException('مرفوض', 'فقط القائد يمكنه رفض الطلب', 403);
        }

        $this->requestRepo->updateStatus($request, JoinRequestStatus::Rejected);
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
