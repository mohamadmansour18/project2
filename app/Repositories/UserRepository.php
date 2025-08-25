<?php

namespace App\Repositories;

use App\Enums\ProfileStudentSpeciality;
use App\Enums\ProfileStudentStatus;
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
                    'phone_number' => $profile->phone_number ?? 'لا يوجد',
                    'student_speciality' => '# ' . $profile->student_speciality,
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

    public function searchStudentByName(string $name): Collection|array
    {
        return User::with(['profile:id,user_id,phone_number,student_speciality,student_status'])
            ->where('role' , UserRole::Student->value)
            ->where('name' , 'LIKE' , $name . '%')
            ->get(['id' , 'university_number' , 'name' , 'email']);
    }

    public function getSortDoctors(?string $sortValue): Collection|array
    {

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

    public function getSortStudents(?string $sortValue): LengthAwarePaginator
    {
        $currentYear = now()->year;

        $query = DB::table('users')
            ->join('profiles', 'users.id', '=', 'profiles.user_id')
            ->where('users.role' , UserRole::Student->value)
            ->whereYear('users.created_at' , $currentYear)
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.university_number',
                'profiles.phone_number',
                'profiles.student_speciality',
                'profiles.student_status',
            ]);

        switch ($sortValue)
        {
            case 'name' :
                $query->orderBy('users.name' , 'asc');
                break;

            case 'university_number' :
                $query->orderBy('users.university_number' , 'desc');
                break;

            case 'student_status' :
                $query->orderByRaw("FIELD(profiles.student_status, ? , ? , ? )" , [ProfileStudentStatus::Fourth_Year->value , ProfileStudentStatus::Successful->value , ProfileStudentStatus::Re_Project->value]);
                break;

            case 'student_speciality' :
                $query->orderByRaw("FIELD(profiles.student_speciality, ? , ? , ? ) DESC , ISNULL(profiles.student_speciality) ASC" , [ProfileStudentSpeciality::Backend->value , ProfileStudentSpeciality::Front_Web->value , ProfileStudentSpeciality::Front_Mobile->value]);
                break;

            default :
                break ;
        }

        $students = $query->paginate(100);

        $students->getCollection()->transform(function($user){
            return [
                'id' => $user->id,
                'university_number'=> $user->university_number,
                'name' => $user->name,
                'email' => $user->email,
                'student_status' => $this->formatStatus($user->student_status) ,
                'phone_number' => $user->phone_number,
                'student_speciality' => '# ' . $user->student_speciality,
            ];
        });

        return $students;
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
    public function getUserWithProfileById(int $id): Model|Collection|Builder|array|null
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

    public function getStudentCurrentYear(): Collection|array
    {
        return User::query()
            ->where('role' , UserRole::Student->value)
            ->whereYear('created_at' , now()->year)
            ->get();
    }

    public function getAvailableDoctors(): Collection|array
    {
        return User::query()
            ->where('role' , UserRole::Doctor->value)
            ->whereDoesntHave('supervisor')
            ->whereDoesntHave('member')
            ->get(['id' , 'name']);
    }

    public function getDoctorInCommitteeCurrentYear(): Collection|array
    {
        return User::query()
            ->where('role' , UserRole::Doctor->value)
            ->where(function($query){
                $query->whereHas('supervisor' , function($query1){
                    $query1->whereYear('created_at' , now()->year);
                })->orWhereHas('member' , function($query2){
                    $query2->whereYear('created_at' , now()->year);
                });
            })
            ->get(['id' , 'name']);
    }

    public function formatStatus(?string $status): ?string
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
}
