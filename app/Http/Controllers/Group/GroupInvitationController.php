<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendGroupInvitationsRequest;
use App\Models\Group;
use App\Services\GroupInvitationService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupInvitationController extends Controller
{
    use ApiSuccessTrait;

    public function __construct(protected GroupInvitationService $invitationService)
    {}

    public function store(SendGroupInvitationsRequest $request, Group $group): JsonResponse
    {
        $this->invitationService->send($group->id, $request->user_id, auth()->user());

        return $this->successResponse('دعوة مرسلة', 'تم إرسال الدعوة بنجاح');
    }

    public function index(): JsonResponse
    {
        $invitations = $this->invitationService->getUserInvitations(auth()->user());

        return $this->dataResponse([
            'invitations' => $invitations,
        ]);
    }

    public function pendingInvitations(int $group): JsonResponse
    {
        $invitations = $this->invitationService->getGroupPendingInvitations($group, auth()->id());
        return $this->dataResponse(['invitations' => $invitations]);
    }

    public function accept(int $invitationId): JsonResponse
    {
        $this->invitationService->accept($invitationId, auth()->user());

        return $this->successResponse('انضمام الى مجموعة', 'تم الانضمام للمجموعة بنجاح');
    }

    public function reject(int $invitationId): JsonResponse
    {
        $this->invitationService->reject($invitationId, auth()->user());

        return $this->successResponse('رفض الانضمام الى مجموعة', 'تم رفض الدعوة بنجاح');
    }

    public function cancel(Request $request, int $invitationId): JsonResponse
    {
        $this->invitationService->cancel($invitationId, $request->user());

        return $this->successResponse('الغاء الدعوة', 'تم إلغاء الدعوة بنجاح');
    }

}
