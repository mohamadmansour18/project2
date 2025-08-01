<?php

namespace App\Http\Controllers\Group;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendJoinRequest;
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

        return $this->successResponse('تم إرسال الطلب', 'تم إرسال طلب الانضمام للمجموعة بنجاح.');
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
        $this->service->accept($requestId, auth()->user());

        return $this->successResponse('تم القبول', 'تم قبول الطلب بنجاح.');
    }

    public function reject(int $requestId): JsonResponse
    {
        $this->service->reject($requestId, auth()->user());

        return $this->successResponse('تم الرفض', 'تم رفض الطلب بنجاح.');
    }

    public function cancel(int $requestId): JsonResponse
    {
        $this->service->cancel($requestId, auth()->user());

        return $this->successResponse('تم الإلغاء', 'تم إلغاء الطلب بنجاح.');
    }


}
