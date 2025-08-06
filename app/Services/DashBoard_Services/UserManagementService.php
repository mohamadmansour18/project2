<?php

namespace App\Services\DashBoard_Services;

use App\Enums\UserRole;
use App\Exceptions\DeleteDoctorException;
use App\Exceptions\PermissionDeniedException;
use App\Helpers\UrlHelper;
use App\Models\User;
use App\Repositories\FormSubmissionPeriodRepository;
use App\Repositories\InterviewCommitteeRepository;
use App\Repositories\ProfileRepository;
use App\Repositories\UserRepository;
use App\Services\FcmNotificationDispatcherService;
use Illuminate\Support\Facades\DB;

class UserManagementService
{

    public function __construct(
        protected UserRepository $userRepository,
        protected ProfileRepository $profileRepository,
        protected FcmNotificationDispatcherService $dispatcherService,
        protected FormSubmissionPeriodRepository $formSubmissionPeriodRepository,
        protected InterviewCommitteeRepository $interviewCommitteeRepository,
    )
    {}

    public function getAllDoctorsDetailed(): array
    {
        $doctors = $this->userRepository->getAllDoctorsWithProfile();

        $results = $doctors->map(function ($doctor) {

            $profile = optional($doctor->profile);

            return [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'email' => $doctor->email,
                'governorate' => $profile->governorate,
                'phone_number' => $profile->phone_number,
                'Registration_date' => $profile->created_at->toDateString(),
                'profile_image' => UrlHelper::imageUrl($profile->profile_image),
            ];
        });

        return ['data' => $results];
    }

    public function getAllStudentsDetailed()
    {
        $result = $this->userRepository->getAllStudentsWithProfile();

        return [
            'data' => $result->items(),
            'current_page' => $result->currentPage(),
            'next_page_url' => $result->nextPageUrl(),
            'last_page' => $result->lastPage(),
            'total' => $result->total(),
        ];
    }

    public function searchDoctorByName(string $name): array
    {
        $doctors = $this->userRepository->searchDoctorByName($name);

        $results = $doctors->map(function($doctor){

            $profile = optional($doctor->profile);
            return [
                'id' => $doctor->id ,
                'name' => $doctor->name,
                'email' => $doctor->email,
                'governorate' => $profile->governorate,
                'phone_number' => $profile->phone_number,
                'created_at' => $doctor->created_at->toDateString(),
                'profile_image' => UrlHelper::imageUrl($profile->profile_image)
            ];
        });

        return ['data' => $results];
    }

    public function sortDoctors(?string $sortValue): array
    {
        $doctors = $this->userRepository->getSortDoctors($sortValue);

        $result = $doctors->map(function($doctor){
            $profile = optional($doctor->profile);

            return [
                'id' => $doctor->id,
                'name' => $doctor->name,
                'email' => $doctor->email,
                'governorate' => $profile->governorate,
                'phone_number' => $profile->phone_number,
                'created_at' => $doctor->created_at->toDateString(),
                'profile_image' => UrlHelper::imageUrl($profile->profile_image)
            ];
        });

        return ['data' => $result];

    }

    public function insertDoctor(array $data): void
    {
        DB::transaction(function () use ($data) {

            $user = $this->userRepository->createUser([
                'name' => $data['name'],
                'email' => $data['email'],
                'role' => UserRole::Doctor,
            ]);

            $imagePath = null ;

            if(!empty($data['profile_image']))
            {
                $image = $data['profile_image'];
                $safePath = $user->id . '_Doctor_' . time() . '.' . $image->getClientOriginalExtension();

                $imagePath = $image->storeAs(
                    'doctor_profile_image' ,
                    $safePath,
                    'public'
                );
            }

            $this->profileRepository->createProfileWithImage($user->id , $imagePath);
        });
    }

    public function importDoctorsFromExcel(array $rows): array
    {
        $inserted = [];
        $failed = [];

        foreach ($rows as $row)
        {
            try {

                if(User::where('email' , $row['email'])->exists())
                {
                    $failed[] = "فشل ترحيل: {$row['name']} - {$row['email']} (السبب : البريد موجود مسبقًا)";
                    continue ;
                }

                DB::beginTransaction();

                $user = $this->userRepository->createUser([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'role' => UserRole::Doctor
                ]);

                $profile = $this->profileRepository->createProfileWithImage($user->id , $row['profile_image']);

                DB::commit();

                $inserted[] = $row['email'];
            }catch (\Throwable $exception)
            {
                DB::rollBack();

                $failed[] = "فشل ترحيل: {$row['name']} - {$row['email']} (خطأ داخلي) ";
            }
        }

        return ['inserted' => $inserted, 'failed' => $failed];
    }

    public function updateDoctorInfo(int $doctorId , array $data): void
    {
        $doctor = User::findOrFail($doctorId);

        if($doctor->role !== UserRole::Doctor)
        {
            throw new PermissionDeniedException('لايمكنك اجراء هذا التعديل' , 'المستخدم الذي تحاول تعديل بياناته ليس دكتورا !' , 403);
        }

        $user = $this->userRepository->updateUser($doctor , $data);

        $messages = [];

        if(isset($data['name']))
        {
            $messages[] = "تم تعديل الاسم إلى: {$data['name']}";
        }

        if (isset($data['email'])) {
            $messages[] = "تم تعديل البريد الإلكتروني إلى: {$data['email']}";
        }

        if(!empty($messages))
        {
            $finalMessage = implode('، ', $messages);

            $this->dispatcherService->sendToUser($doctor , 'تم تعديل بياناتك' ,"قام رئيس القسم {$finalMessage}");
        }
    }

    public function deleteDoctorById(int $doctorId): void
    {
        $doctor = $this->userRepository->getDoctorWithProfileById($doctorId);

        if($doctor->role !== UserRole::Doctor)
        {
            throw new DeleteDoctorException('لايمكنك حذف المستخدم !' , 'هذا المستخدم المحدد ليس دكتورا في النظام' , 422);
        }

        if($this->formSubmissionPeriodRepository->isInForm1PeriodNow())
        {
            throw new DeleteDoctorException('لايمكنك حذف المستخدم !' , 'لايمكنك حذف الدكتور اثناء فترة التقديم على الاستمارة واحد' , 422);
        }

        if($this->interviewCommitteeRepository->isDoctorInInterviewCommitteeThisYear($doctorId))
        {
            $interviewPeriod = $this->formSubmissionPeriodRepository->getCurrentInterviewPeriod();

            if(!$interviewPeriod)
            {
                throw  new DeleteDoctorException('لايمكنك حذف المستخدم !' , 'لايمكنك حذف الدكتور لانه ضمن لجان المقابلة ولم يتم تحديد موعد المقابلات بعد , قم بحذفه من اللجنة اولا' , 422);
            }

            if(now()->between($interviewPeriod->start_date , $interviewPeriod->end_date))
            {
                throw new DeleteDoctorException('لايمكنك حذف المستخدم !' , 'لايمكنك حذف الدكتور اثناء فترة المقابلات' , 422);
            }

            if(now() <= $interviewPeriod->start_date)
            {
                throw new DeleteDoctorException('لايمكنك حذف المستخدم !' , 'لايمكنك حذف الدكتور في الفترة قبل بدء موعد المقابلات' , 422);
            }
        }

        $this->userRepository->softDeleteUserWithProfile($doctor);
    }
}
