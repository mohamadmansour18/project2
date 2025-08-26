<?php

namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;
use App\Http\Requests\DoctorSearchRequest;
use App\Repositories\SearchHistoryRepository;
use App\Services\SearchHistoryService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

class SearchHistoryController extends Controller
{
    use ApiSuccessTrait;
    public function __construct(
        protected SearchHistoryService $searchHistoryService
    )
    {}

    public function getUserSearchHistory(): JsonResponse
    {
        $data = $this->searchHistoryService->getUserHistory();

        return $this->dataResponse($data , 200);
    }

    public function search(DoctorSearchRequest $request): JsonResponse
    {
        $data = $this->searchHistoryService->search($request->search);

        return response()->json($data , 200);
    }

    public function deleteItem(int $itemId): JsonResponse
    {
        $this->searchHistoryService->delete($itemId);

        return $this->successResponse('تمت العملية بنجاح !' , 'تم حذف هذا العنصر من سجل البحث بنجاح' , 200);
    }
}
