<?php

namespace App\Services\DashBoard_Services;

use App\Enums\GroupMemberRole;
use App\Enums\ProfileStudentSpeciality;
use App\Enums\ProfileStudentStatus;
use App\Exceptions\ProjectManagementException;
use App\Helpers\UrlHelper;
use App\Repositories\GroupRepository;
use App\Repositories\ProjectForm2Repository;
use App\Repositories\ProjectFormRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Mpdf\Exception\FontException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GroupManagementService
{
    public function __construct(
        protected  GroupRepository $groupRepository,
        protected ProjectFormRepository $projectFormRepository,
        protected ProjectForm2Repository $projectForm2Repository,
    )
    {}

    public function getGroupDetails(int $groupId): array
    {
        $group = $this->groupRepository->getGroupWithRelationWeb($groupId);

        if(!$group)
        {
            return [
                'Main' => [],
                'group_member' => [],
                'interview_information' => [],
                'forms_information' => [],
                'project_grade' => [],
            ];
        }

        //1: Main
        $leader = $group->members->where('role' , GroupMemberRole::Leader)->first()?->user;
        $main = [
            'group_name' => $group->name ?? "لا يوجد اسم لهذا الغروب",
            'leader_name' => $leader?->name ?? "لايوجد مشرف للغروب",
            'members_count' => $this->getCustomGroupNumber($group) ?? "عدد الاعضاء صفر"
        ];

        //2: Group Members
        $groupMembers = $group->members->map(function ($member) {
            $user = $member->user;
            $profile = $user?->profile;

            return [
                'name' => $user?->name ?? 'لايوجد اسم',
                'image' => UrlHelper::imageUrl($profile?->profile_image) ?? null,
                'speciality' => $profile && $profile->student_speciality ? $this->formatSpeciality($profile->student_speciality->value) : '',
                'status' => $profile && $profile->student_status ? $this->formatStatus($profile->student_status->value) : '',
            ];
        })->values();

        //3: Interview information
        $interview = $group->interviewSchedule;

        $interviewInfo = $interview ? [
            'date' => $interview->interview_date ? Carbon::parse($interview->interview_date)->format('d/m/Y') : "لم يحدد بعد",
            'time' => $interview->interview_time ? $this->formatTime($interview->interview_time) : "لم يحدد بعد",
            'day'  => $interview->interview_date ? $this->getDayName($interview->interview_date) : "لم يحدد بعد",
        ] : [];

        //4: Forms information
        $form1 = $group->projectForm;
        $form2 = $group->projectForm2;
        $formsInfo = [];

        if($form1 || $form2)
        {
            $formsInfo = [
                'supervisor_name' => $form1?->users?->name ?? "لايوجد" ,
                'form1_date' => $form1?->created_at?->toDateString() ?? 'لم تقدم بعد',
                'form2_date' => $form2?->created_at?->toDateString() ?? 'لم تقدم بعد',
            ];
        }

        //5: Project grade
        $grade = $group->projectGrade;
        $projectGrade = [];
        if($grade)
        {
            $committee = $grade->committee;
            $committeeData = [
                'supervisor' => [
                    'name' => $committee?->adminSupervisor?->name ?? 'لايوجد اسم',
                    'image' => UrlHelper::imageUrl($committee?->adminSupervisor?->profile?->profile_image) ?? null,
                ],
                'member' => [
                    'name' => $committee?->adminMember?->name ?? 'لايوجد اسم',
                    'image' => UrlHelper::imageUrl($committee?->adminMember?->profile?->profile_image) ?? null,
                ],
            ];

            $excludedStudents = $grade->GradeExceptions->map(function ($exception) {
                $student = $exception->user;
                return [
                    'name' => $student?->name ?? 'لايوجد اسم',
                    'image' => UrlHelper::imageUrl($student?->profile?->profile_image) ?? null,
                ];
            });

            $studentWithGrades = $group->members->filter(function ($member) use ($excludedStudents) {
                return !$excludedStudents->contains(fn($e) => $e['name'] === $member->user?->name);
            })->map(function ($member) {
                return [
                    'name' => $member->user?->name ?? 'لايوجد اسم',
                    'image' => UrlHelper::imageUrl($member->user?->profile?->profile_image) ?? null,
                ];
            });

            $projectGrade = [
                'presentation_grade' => $grade->presentation_grade ?? '',
                'project_grade' => $grade->project_grade ?? '',
                'total_grade' => $grade->total_grade ?? '',
                'students' => $studentWithGrades->values(),
                'committee' => $committeeData,
                'excluded_students' => $excludedStudents->values(),
            ];
        }
        return [
            'Main' => $main ?: [],
            'group_member' => $groupMembers ?: [],
            'interview_information' => $interviewInfo ?: [],
            'forms_information' => $formsInfo ?: [],
            'project_grade' => $projectGrade ?: [],
        ];
    }

    public function downloadForm1(int $groupId): StreamedResponse
    {
        $form = $this->projectFormRepository->getFormByGroupId($groupId);

        if(!$form || !$form->filled_form_file_path)
        {
            throw new ProjectManagementException('لا يمكنك اجراء هذه العملية !' , 'الاستمارة التي تحاول تنزيلها غير موجودة' , 404);
        }

        $filePath = $form->filled_form_file_path;
        if(!Storage::disk('public')->exists($filePath))
        {
            throw new ProjectManagementException('لا يمكنك اجراء هذه العملية !' , 'الاستمارة التي تحاول تنزيلها غير موجودة ضمن ملفات النظام' , 404);
        }

        return Storage::disk('public')->download($filePath);
    }

    public function downloadForm2(int $groupId): StreamedResponse
    {
        $form = $this->projectForm2Repository->getFormByGroupId($groupId);

        if(!$form || !$form->filled_form_file_path)
        {
            throw new ProjectManagementException('لا يمكنك اجراء هذه العملية !' , 'الاستمارة التي تحاول تنزيلها غير موجودة' , 404);
        }

        $filePath = $form->filled_form_file_path;
        if(!Storage::disk('public')->exists($filePath))
        {
            throw new ProjectManagementException('لا يمكنك اجراء هذه العملية !' , 'الاستمارة التي تحاول تنزيلها غير موجودة ضمن ملفات النظام' , 404);
        }

        return Storage::disk('public')->download($filePath);
    }


    //--------------------->>>>>>>>>>[HELPERS]<<<<<<<<<<---------------------//
    private function getCustomGroupNumber($group): ?string
    {
        $numberOfMembers = $group->number_of_members;

        return $numberOfMembers > 5 ? "6/$numberOfMembers" : "5/$numberOfMembers";
    }

    private function getDayName($date): string
    {
        $days = [
            'Saturday' => 'السبت',
            'Sunday' => 'الاحد',
            'Monday' => 'الاثنين',
            'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الاربعاء',
            'Thursday' => 'الخميس',
            'Friday' => 'الجمعة',
        ];
        return $days[Carbon::parse($date)->format('l')] ?? 'لم يحدد بعد';
    }

    private function formatTime($time): string
    {
        $carbon = Carbon::createFromFormat('H:i:s', $time);
        $formatted = $carbon->format('h:i');
        $suffix = $carbon->format('A') === 'AM' ? 'ص' : 'م';
        return $formatted . $suffix;
    }

    public function formatStatus(?string $status): ?string
    {
        if($status == ProfileStudentStatus::Fourth_Year->value)
        {
            return 'سنة رابعة';
        }
        return 'اعادة مشروع';
    }

    public function formatSpeciality(?string $speciality): ?string
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
