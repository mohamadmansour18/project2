<?php

namespace App\Services;

use App\Enums\ProfileStudentStatus;
use App\Exceptions\GradeException;
use App\Models\InterviewCommittee;
use App\Models\InterviewSchedule;
use App\Models\ProjectGrade;
use App\Repositories\GradeExceptionRepository;
use App\Repositories\GroupGradeRepository;
use App\Repositories\GroupMemberRepository;
use App\Repositories\InterviewCommitteeRepository;
use App\Repositories\ProfileRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupGradeService
{
    public function __construct(
        protected GroupGradeRepository $groupGradeRepository,
        protected GroupMemberRepository $groupMemberRepository,
        protected ProfileRepository $profileRepository,
        protected GradeExceptionRepository $gradeExceptionRepository,
        protected InterviewCommitteeRepository $interviewCommitteeRepository
    )
    {}

    public function createGrade(array $data): void
    {
        $committeeId = InterviewSchedule::where('group_id' , $data['group_id'])
            ->pluck('committee_id')
            ->first();

        if(!$committeeId)
        {
            throw new GradeException('خطأ خاص بعملية التحقق !' , 'هذا الغروب غير مسجل في اي لجنة' , 404);
        }

        $committee = InterviewCommittee::find($committeeId);
        if($committee->supervisor_id !== Auth::id())
        {
            throw new GradeException('غير مصرح بك !' , 'عذرا غير مصرح بك باضافة علامة لهذا الغروب فقط الدكتور المشرف يسمح له بذلك' , 403);
        }

        $existing = ProjectGrade::query()
            ->where('committee_id' , $committeeId)
            ->where('group_id' , $data['group_id'])
            ->first();

        if($existing)
        {
            throw new GradeException('لايمكنك اجراء هذه العملية !' , 'تم ادخال علامة لهذا الغروب مسبقا' , 400);
        }

        DB::transaction(function () use ($committeeId, $data){
            $grade = $this->groupGradeRepository->createGrade($committeeId , $data);

            $groupId = $grade['group_id'];
            $total = $grade['total_grade'];

            $studentIds = $this->groupMemberRepository->getGroupMemberIds($groupId);


            if($total < 60)
            {
                $this->profileRepository->updateStudentStatus($studentIds , ProfileStudentStatus::Re_Project);
            }

            elseif ($total >= 60 && count($data['exceptions']) > 0)
            {
                $this->profileRepository->updateStudentStatus($data['exceptions'] , ProfileStudentStatus::Re_Project);
            }else {
                $this->profileRepository->updateStudentStatus($studentIds, ProfileStudentStatus::Successful);
            }
        });

    }

    public function updateGrade(array $data): void
    {
        $doctorId = Auth::id();

        $grade = $this->groupGradeRepository->getGradeByGroup($data['group_id']);

        if(!$grade)
        {
            throw new GradeException('لايمكن اجراء العملية !' , 'لا يوجد علامة سابقة لهذا الغروب ليتم تعديلها' , 404);
        }

        $isSupervisor = $this->groupGradeRepository->isSupervisorForCommittee($doctorId , $grade->committee_id);

        if(!$isSupervisor)
        {
            throw new GradeException('! لايمكنك اجراء العملية' , 'غير مصرح لك بتعديل علامة الغروب' , 403);
        }

        $committee = $this->interviewCommitteeRepository->findOrFillById($grade->committee_id);

        $endDateTime = Carbon::parse($committee->days . ' ' . $committee->end_interview_time);

        if(now()->greaterThan($endDateTime))
        {
            throw new GradeException('! لايمكنك اجراء العملية', 'لقد انتهى وقت المقابلات الخاصة باللجنة ولا يمكن تعديل العلامة بعد الآن', 422);
        }

        if($grade->is_edited)
        {
            throw new GradeException('! لايمكنك اجراء العملية' , 'تم تعديل العلامة مسبقا ولايمكن تعديلها مرة اخرى' , 422);
        }

        DB::transaction(function () use ($grade, $data){
            $grade = $this->groupGradeRepository->updateGrade($grade , $data);

            $total = $grade['total_grade'];

            $studentIds = $this->groupMemberRepository->getGroupMemberIds($grade['group_id']);
            $studentExceptionIds = $this->gradeExceptionRepository->getExceptionStudentIdsByGrade($grade['id']) ?? [];

            $nonExceptionIds = array_values(array_diff($studentIds, $studentExceptionIds));

            if ($total < 60) {
                $this->profileRepository->updateStudentStatus($nonExceptionIds, ProfileStudentStatus::Re_Project);
            } else {
                $this->profileRepository->updateStudentStatus($nonExceptionIds, ProfileStudentStatus::Successful);
            }
        });

    }
}
