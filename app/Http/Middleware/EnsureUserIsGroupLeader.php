<?php

namespace App\Http\Middleware;

use App\Enums\GroupInvitationStatus;
use App\Enums\JoinRequestStatus;
use App\Models\GroupInvitation;
use App\Models\JoinRequest;
use App\Repositories\GroupMemberRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsGroupLeader
{
    public function __construct(protected GroupMemberRepository $groupMemberRepo) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $groupId = $this->extractGroupIdFromRequest($request);

        if (!$groupId || !$this->groupMemberRepo->isLeader($groupId, $user->id)) {
            return $this->forbiddenResponse();
        }

        return $next($request);
    }

    /**
     * Extract group ID from request based on route or input
     */
    private function extractGroupIdFromRequest(Request $request): ?int
    {
        // Case 1: Explicit group in route
        if ($request->route('group')) {
            $group = $request->route('group');
            return is_object($group) ? $group->id : (int) $group;
        }

        // Case 2: Join request
        if ($request->routeIs('join-request.*') || str_contains($request->path(), 'join-request')) {
            return $this->handleJoinRequest($request);
        }

        // Case 3: Invitation
        if ($request->routeIs('invitations.*') || str_contains($request->path(), 'invitations')) {
            return $this->handleInvitation($request);
        }

        // Case 4: Project form (e.g. /project-form-one/{form}/submit)
        if ($request->route('form') && is_object($request->route('form'))) {
            return $request->route('form')->group_id;
        }

        // Case 5: New form creation (group_id from input)
        if ($request->has('group_id')) {
            return (int) $request->input('group_id');
        }

        return null;
    }

    private function handleJoinRequest(Request $request): ?int
    {
        $joinRequestId = $request->route('id') ?? $request->route('requestId');

        $joinRequest = JoinRequest::find($joinRequestId);

        if (!$joinRequest) {
            abort(404, 'الطلب غير موجود.');
        }

        if (!in_array($joinRequest->status, [JoinRequestStatus::Pending, JoinRequestStatus::PendingLeader])) {
            abort(404, 'تم التعامل مع الطلب.');
        }

        return $joinRequest->group_id;
    }


    private function handleInvitation(Request $request): ?int
    {
        $invitation = GroupInvitation::find($request->route('invitation'));

        if (!$invitation) {
            abort(404, 'الدعوة غير موجودة.');
        }

        if ($invitation->status !== GroupInvitationStatus::Pending) {
            abort(404, 'تم التعامل مع الدعوة.');
        }

        return $invitation->group_id;
    }

    private function forbiddenResponse(): Response
    {
        return response()->json([
            'title' => 'غير مصرح لك بالدخول !',
            'body' => 'ليس لديك صلاحية للوصول إلى هذا المورد',
            'statusCode' => 403,
        ], 403);
    }
}
