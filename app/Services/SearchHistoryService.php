<?php

namespace App\Services;

use App\Enums\ProfileStudentSpeciality;
use App\Enums\ProfileStudentStatus;
use App\Helpers\UrlHelper;
use App\Repositories\SearchHistoryRepository;
use Illuminate\Support\Facades\Auth;

class SearchHistoryService
{
    public function __construct(
        protected SearchHistoryRepository $searchHistoryRepository,
    )
    {}

    public function getUserHistory(): array
    {
        $userId = Auth::id();

        $history = $this->searchHistoryRepository->getByUser($userId)->map(fn($item)=>[
            'id' => $item->id,
            'query' => $item->query,
        ])->toArray();

        return [
            "searchHistory" => $history,
            "statusCode" => 200
        ];

    }

    public function search(string $keyword): array
    {
        $this->searchHistoryRepository->store([
            'user_id' => Auth::id(),
            'query' => $keyword,
        ]);

        $students = $this->searchHistoryRepository->searchStudentByName($keyword);

        return $students->map(fn($student) => [
            'id' => $student->id,
            'name' => $student->name ?? '',
            'image' => UrlHelper::imageUrl($student->profile?->profile_image) ?? null,
            'speciality' => $this->formatSpeciality($student->profile?->student_speciality?->value) ?? '',
            'status'     => $this->formatStatus($student->profile?->student_status?->value) ?? ''
        ])->toArray();
    }

    public function delete(int $itemId): void
    {
        $userId = Auth::id();
        $this->searchHistoryRepository->findOrFail($itemId);
        $this->searchHistoryRepository->deleteById($itemId , $userId);
    }

    private function formatStatus(?string $status): ?string
    {
        if($status == ProfileStudentStatus::Fourth_Year->value)
        {
            return 'سنة رابعة';
        }
        if($status == ProfileStudentStatus::Successful->value)
        {
            return 'ناجح في المشروع';
        }
        return 'اعادة مشروع';
    }

    private function formatSpeciality(?string $speciality): ?string
    {
        switch ($speciality){
            case ProfileStudentSpeciality::Backend->value:
                $value = "باك ايند #";
                break;

            case ProfileStudentSpeciality::Front_Mobile->value :
                $value = "فرونت موبايل #";
                break;

            case ProfileStudentSpeciality::Front_Web->value :
                $value = "فرونت ويب #";
                break;

            default :
                $value = '';
        }
        return $value;
    }
}
