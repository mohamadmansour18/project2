<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

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
