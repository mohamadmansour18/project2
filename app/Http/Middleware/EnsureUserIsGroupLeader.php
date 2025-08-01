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
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function __construct(protected GroupMemberRepository $groupMemberRepo)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $groupId = null;

        if ($request->route('group')) {
            $group = $request->route('group');
            $groupId = is_object($group) ? $group->id : $group;
        }

        elseif ($request->routeIs('join-request.*') || str_contains($request->path(), 'join-request')) {
            $joinRequest = JoinRequest::find($request->route('id'));
            if (!$joinRequest ) {
                return response()->json([
                    'title' => 'غير موجود!',
                    'body' => 'الطلب غير موجود.',
                    'statusCode' => 404
                ], 404);
            }
            if($joinRequest->status !== JoinRequestStatus::Pending){
                return response()->json([
                    'title' => 'غير موجود',
                    'body' => 'تم التعامل مع الطلب.',
                    'statusCode' => 404
                ], 404);
            }
            $groupId = $joinRequest->group_id;
        }

        elseif ($request->routeIs('invitations.*') || str_contains($request->path(), 'invitations')) {
            $invitation = GroupInvitation::find($request->route('invitation'));
            if (!$invitation) {
                return response()->json([
                    'title' => 'غير موجود!',
                    'body' => 'الدعوة غير موجودة.',
                    'statusCode' => 404
                ], 404);
            }
            if($invitation->status !== GroupInvitationStatus::Pending){
                return response()->json([
                    'title' => 'غير موجودة',
                    'body' => 'تم التعامل مع الدعوة.',
                    'statusCode' => 404
                ], 404);
            }
            $groupId = $invitation->group_id;
        }

        if (!$groupId || !$this->groupMemberRepo->isLeader($groupId, $user->id)) {
            return response()->json([
                'title' => 'غير مصرح لك بالدخول !',
                'body' => 'ليس لديك صلاحية للوصول إلى هذا المورد',
                'statusCode' => 403
            ], 403);
        }

        return $next($request);
    }
}
