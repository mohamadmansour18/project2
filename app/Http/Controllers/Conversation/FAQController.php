<?php

namespace App\Http\Controllers\Conversation;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateFAQRequest;
use App\Models\FAQ;
use App\Services\FAQService;
use App\Traits\ApiSuccessTrait;
use Illuminate\Http\JsonResponse;

class FAQController extends Controller
{
    use ApiSuccessTrait;

    public function __construct(
        protected FAQService $faqService,
    )
    {}

    public function showFAQs(): JsonResponse
    {
        $data = $this->faqService->getFaqs();

        return response()->json($data , 200);
    }

    public function createFAQ(CreateFAQRequest $request): JsonResponse
    {
        $this->faqService->createFaq($request->validated());

        return $this->successResponse('تمت العملية بنجاح !' ,'تم اضافة هذا السؤال واجابته الى نظام المجيب الالي بنجاح' , 201);

    }

    public function deleteFAQ(int $questionId): JsonResponse
    {
        $this->faqService->deleteFaq($questionId);

        return $this->successResponse('تمت العملية بنجاح !' ,'تم حذف هذا السؤال واجابته من نظام المجيب الالي بنجاح' , 200);
    }
}
