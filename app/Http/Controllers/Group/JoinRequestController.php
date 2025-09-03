<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendJoinRequest;
use App\Http\Requests\SendSixthMemberRequest;
use App\Services\JoinRequestService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

class JoinRequestController extends Controller
{
    use ApiSuccessTrait;

    public function __construct(protected JoinRequestService $service) {}

    public function store(int $groupId): JsonResponse
    {
        $this->service->send($groupId, auth()->user());

        return $this->successResponse('! تم إرسال الطلب', 'تم إرسال طلب الانضمام للمجموعة بنجاح.');
    }

    public function index(int $groupId): JsonResponse
    {
        $requests = $this->service->getPendingForGroup($groupId);

        return $this->dataResponse(['requests' => $requests]);
    }

    public function myRequests(): JsonResponse
    {
        $requests = $this->service->getUserPendingRequests(auth()->user());

        return $this->dataResponse(['requests' => $requests]);
    }

    public function accept(int $requestId): JsonResponse
    {
        $this->service->acceptUnified($requestId, auth()->user());

        return $this->successResponse('! تمت العملية', 'تم التعامل مع الطلب حسب حالة المجموعة.');
    }

    public function reject(int $requestId): JsonResponse
    {
        $this->service->rejectUnified($requestId, auth()->user());

        return $this->successResponse('! تم الرفض', 'تم رفض الطلب بنجاح.');
    }

    public function cancelByGroup(int $groupId): JsonResponse
    {
        $this->service->cancelByGroup($groupId, auth()->user());

        return $this->successResponse('! تم الإلغاء', 'تم إلغاء الطلب بنجاح.');
    }

    public function storeSixthMemberRequest(int $groupId, SendSixthMemberRequest $request): JsonResponse
    {
        $this->service->sendSixthMemberRequest($groupId, auth()->user(), $request->description);

        return $this->successResponse('! تم إرسال الطلب', 'تم إرسال طلب الانضمام كمشارك سادس بنجاح.');
    }

    // -------------------- الليدر --------------------
    public function leaderRequests(int $groupId): JsonResponse
    {
        $requests = $this->service->getLeaderPendingRequests($groupId);
        return $this->dataResponse(['requests' => $requests]);
    }

    public function leaderApprove(int $requestId): JsonResponse
    {
        $this->service->leaderApprove($requestId, auth()->id());
        return $this->successResponse('! تم إرسال الطلب لرئيس القسم', 'تمت الموافقة على الطلب وتحويله لرئيس القسم.');
    }

    public function leaderReject(int $requestId): JsonResponse
    {
        $this->service->leaderReject($requestId, auth()->id());
        return $this->successResponse('! تم الرفض', 'تم رفض الطلب من قبل الليدر.');
    }

    // -------------------- رئيس القسم --------------------
    public function headRequests(): JsonResponse
    {
        $requests = $this->service->getHeadPendingRequests();
        return $this->dataResponse(['requests' => $requests]);
    }

    public function headApprove(int $requestId): JsonResponse
    {
        $this->service->headApprove($requestId);
        return $this->successResponse('! تمت الموافقة', 'تمت الموافقة على الطلب والانضمام للمجموعة.');
    }

    public function headReject(int $requestId): JsonResponse
    {
        $this->service->headReject($requestId);
        return $this->successResponse('! تم الرفض', 'تم رفض الطلب من قبل رئيس القسم.');
    }


}
