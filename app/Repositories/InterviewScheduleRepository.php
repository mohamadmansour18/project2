<?php

namespace App\Repositories;

use App\Enums\ProjectFormStatus;
use App\Models\Group;
use App\Models\InterviewCommittee;
use App\Models\InterviewPeriod;
use App\Models\InterviewSchedule;
use Illuminate\Database\Eloquent\Collection;

class InterviewScheduleRepository
{
    public function countInterviewGroupsForCurrentYear()
    {
        return InterviewSchedule::whereYear('created_at' , now()->year)
            ->distinct('group_id')
            ->count('group_id');
    }

    //------------------>>>>>>>>>>[FUNCTION RELATED OF FINAL INTERVIEW]<<<<<<<<<<------------------//


    /**  يقوم بجلب موعد المقابلات النهائية للسنة الحالية */
    public function getCurrentYearPeriod()
    {
        return InterviewPeriod::whereYear('created_at' , now()->year)
                              ->first();
    }

    /** يقوم بجلب لجان المقابلات للسنة الحالية مرتبين حسب ال id */
    public function getUnassignedCommitteesForYear(): Collection|array
    {
        return InterviewCommittee::with(['adminSupervisor' , 'adminMember'])
            ->whereYear('created_at' , now()->year)
            ->whereNull('days')
            ->orderBy('id')
            ->get();
    }

    /** يتحقق هل يوجد مقابلات مخزنة لهذه السنة الحالية مسبقا ؟ */
    public function interviewSchedulesExistForYear()
    {
        return InterviewSchedule::whereYear('interview_date' , now()->year)->exists();
    }

    /** جلب غروبات المقابلات بشروط معينة : ان يكون عدد اعضاء الغروب اكبر من 2 وان يكون الغروب قد قدم استمارة واحد وتم قبولها وان يكون قد قدم استمارة اثنان */
    public function getEligibleGroupsForYear(): Collection|array
    {
        return Group::query()
            ->whereYear('created_at' , now()->year)
            ->where('number_of_members' , '>' , 2)
            ->whereHas('projectForm' , fn($query) => $query->where('status' , ProjectFormStatus::Approved->value))
            ->whereHas('projectForm2')
            ->orderBy('id')
            ->get(['id' , 'name']);

    }

    /** تحديث لجنة واحدة بقيم اليوم (اي اليوم الذي ستقابل فيه اللجنة) بالاضافة الى بدء وانتهاء وقت كل لجنة */
    public function updateCommitteeAssignment(InterviewCommittee $committee, string $dateYmd, \DateTimeInterface $startTs, \DateTimeInterface $endTs): void
    {
        $committee->days = $dateYmd;                 // Date (Y-m-d)
        $committee->start_interview_time = $startTs; // timestamp/datetime
        $committee->end_interview_time   = $endTs;   // timestamp/datetime
        $committee->save();
    }

    /** انشاء مواعيد المقابلات لكل لجنة عبر العلاقة schedules وتخزين النتائج في جدول ال Interview_Schedules ويكون :
     * rows = [
     *   ['group_id'=>..,'interview_date'=>..,'interview_time'=>..,'interview_end_time'=>.., 'created_at'=>..,'updated_at'=>..],
     *   ...
     * ]
     */
    public function createSchedulesForCommittee(InterviewCommittee $committee, array $rows): void
    {
        $committee->schedules()->createMany($rows);
    }

    /** يقوم بجلب كل المقابلات مع لجنة كل مقابلة وغروب كل لجنة من اجل توليد ملف ال PDF بهذه البيانات */
    public function getSchedulesForYearWithRelations(): Collection|array
    {
        return InterviewSchedule::with([
                'committee.adminSupervisor',
                'committee.adminMember',
                'group'
            ])
            ->whereYear('interview_date' , now()->year)
            ->orderBy('interview_date')
            ->orderBy('committee_id')
            ->orderBy('interview_time')
            ->get();
    }

    /////// -----------------------> [DELETE]
    public function deleteSchedulesForYear()
    {
        $currentYear = now()->year ;

        return InterviewSchedule::whereYear('interview_date', $currentYear)->delete();
    }

    public function resetCommitteesForYear()
    {
        $currentYear = now()->year ;

        return InterviewCommittee::whereYear('created_at' , $currentYear)
            ->update([
                'days'                  => null,
                'start_interview_time'  => null,
                'end_interview_time'    => null,
            ]);
    }
}
