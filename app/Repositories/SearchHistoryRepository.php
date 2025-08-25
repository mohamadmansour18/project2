<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Models\SearchHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class SearchHistoryRepository
{
    public function store(array $data): SearchHistory
    {
        return SearchHistory::create($data);
    }

    public function findOrFail(int $itemId): SearchHistory
    {
        return SearchHistory::findOrFail($itemId);
    }

    public function getByUser(int $userId): Collection|array
    {
        return SearchHistory::query()
            ->where('user_id' , $userId)
            ->latest()
            ->get();
    }

    public function searchStudentByName(string $keyword): Collection|array
    {
        return User::query()
            ->where('role' , UserRole::Student->value)
            ->where('name' , 'LIKE' , $keyword . '%' )
            ->with('profile:id,user_id,student_speciality,student_status,profile_image')
            ->get(['id' , 'name' , 'role']);
    }

    public function deleteById(int $itemId , int $userId)
    {
        return SearchHistory::where('id' , $itemId)
            ->where('user_id' , $userId)
            ->delete();
    }
}
