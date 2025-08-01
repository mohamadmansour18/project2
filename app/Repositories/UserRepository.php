<?php

namespace App\Repositories;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Collection;
use App\Enums\UserRole;
use App\Models\User;

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
            ->get(['id , name']);
    }


}
