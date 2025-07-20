<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendGroupInvitationsRequest;
use App\Services\GroupInvitationService;
use App\Traits\ApiSuccessTrait;

class GroupInvitationController extends Controller
{
    use ApiSuccessTrait;

    protected GroupInvitationService $invitationService;

    public function __construct(GroupInvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
    }

    public function store(SendGroupInvitationsRequest $request)
    {
        $this->invitationService->sendInvitation(
            $request->group_id,
            $request->user_id,
            auth()->user()
        );

        return $this->successResponse('دعوة مرسلة', 'تم إرسال الدعوة بنجاح');
    }

    public function index()
    {
        $invitations = $this->invitationService->getUserInvitations(auth()->user());

        return $this->dataResponse([
            'invitations' => $invitations
        ]);
    }

}
