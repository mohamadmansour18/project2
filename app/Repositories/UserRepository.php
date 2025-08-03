<?php

namespace App\Repositories;

use App\Models\Profile;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserRepository
{
    public function getStudentCountForCurrentYear(): int
    {
        return User::query()->where('role' , UserRole::Student->value)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    public function getDoctorCount(): int
    {
        return User::query()->where('role' , UserRole::Doctor->value)->count();
    }

    public function findById(int $id): ?User
    {
        return User::find($id);
    }

    public function getStudentsForCurrentYear(): Collection
    {
        $currentYear = Carbon::now()->year;

        return User::where('role', UserRole::Student->value)
            ->whereDoesntHave('groupMember')
            ->whereYear('created_at', $currentYear)
            ->whereNotNull('email_verified_at')
            ->with('profile')
            ->get();
    }

    public function getStudentCountForCurrentYearDynamic($year): int
    {
        return User::query()->where('role' , UserRole::Student->value)
            ->whereYear('created_at', $year)
            ->count();
    }

    public function getDoctorCountForCurrentYearDynamic($year): int
    {
        return User::query()->where('role' , UserRole::Doctor->value)
            ->whereYear('created_at', $year)
            ->count();
    }

    public function getDoctorSpecificDataForAdminHomePage(): Collection|array
    {
        return User::with('profile')
            ->where('role' , UserRole::Doctor->value)
            ->get(['id' , 'name']);
    }

    public function getAllDoctorsWithProfile(): Collection|array
    {
        return User::with('profile')
            ->where('role' , UserRole::Doctor->value)
            ->get(['id' , 'name' , 'email' , 'created_at']);
    }

    public function searchDoctorByName(string $name): Collection|array
    {
        return User::with('profile')
            ->where('role' , UserRole::Doctor->value)
            ->where('name' , 'LIKE' , $name . '%')
            ->get(['id' , 'name' , 'email' , 'created_at']);
    }

    public function getSortDoctors(?string $sortValue): Collection|array
    {
        $allowedSort = ['name' , 'email' , 'created_at'];

        if(!in_array($sortValue , $allowedSort))
        {
            $sortValue = 'name' ;
        }

        $query = User::with('profile')->where('role' , UserRole::Doctor->value);

        switch ($sortValue)
        {
            case 'created_at' :
                $query->orderBy('created_at' , 'desc');
                break;

            default :
                $query->orderBy($sortValue , 'asc');
                break;
        }

        return $query->get(['id' , 'name' , 'email' , 'created_at']);
    }

    public function createUser(array $data): User
    {
        return User::create($data);
    }
}
