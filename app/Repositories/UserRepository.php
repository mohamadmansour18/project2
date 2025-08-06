<?php

namespace App\Repositories;

use App\Models\InterviewCommittee;
use App\Models\Profile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
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

    public function getAllStudentsWithProfile(): LengthAwarePaginator
    {
        $currentYear = now()->year;

        return User::with(['profile:id,user_id,phone_number,student_speciality,student_status'])
            ->where('role' , UserRole::Student->value)
            ->whereYear('created_at', $currentYear)
            ->select(['id', 'name', 'university_number', 'email'])
            ->paginate(100)
            ->through(function($user){

                $profile = optional($user->profile);

                return [
                    'id' => $user->id ,
                    'university_number' => $user->university_number ,
                    'name' => $user->name ,
                    'email' => $user->email ,
                    'student_status' => $profile->student_status,
                    'phone_number' => $profile->phone_number,
                    'student_speciality' => $profile->student_speciality,
                ];
            });
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

    public function updateUser(User $user, array $data): User
    {
        $user->update($data);
        return $user;
    }

    /**
     * @return User
     */
    public function getDoctorWithProfileById(int $id): Model|Collection|Builder|array|null
    {
        return User::with('profile')->findOrFail($id);
    }

    public function softDeleteUserWithProfile(User $user): void
    {
        DB::transaction(function() use ($user){
            $user->profile?->delete();
            $user->delete();
        });
    }


}
