<?php

namespace App\Services;

use App\Exceptions\GradeException;
use App\Models\InterviewCommittee;
use App\Models\InterviewSchedule;
use App\Models\ProjectGrade;
use App\Repositories\GroupGradeRepository;
use Illuminate\Support\Facades\Auth;

class GroupGradeService
{
    public function __construct(
        protected GroupGradeRepository $groupGradeRepository
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
            throw new GradeException('غير مصرح بك !' , 'عذرا غير مصرح بك باضافة علامة لهذا الغروب فقط الدكتور المشرف' , 403);
        }

        $existing = ProjectGrade::query()
            ->where('committee_id' , $committeeId)
            ->where('group_id' , $data['group_id'])
            ->first();

        if($existing)
        {
            throw new GradeException('لايمكنك اجراء هذه العملية !' , 'تم ادخال علامة لهذا الغروب مسبقا' , 400);
        }

        $grade = $this->groupGradeRepository->createGrade($committeeId , $data);
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
            throw new GradeException('لايمكنك اجراء العملية !' , 'غير مصرح لك بتعديل علامة الغروب' , 403);
        }

        if($grade->is_edited)
        {
            throw new GradeException('لايمكنك اجراء العملية !' , 'تم تعديل العلامة مسبقا ولايمكن تعديلها مرة اخرى' , 422);
        }

        $this->groupGradeRepository->updateGrade($grade , $data);

    }
}
