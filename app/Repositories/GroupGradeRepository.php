<?php

namespace App\Repositories;

use App\Models\GradeException;
use App\Models\InterviewCommittee;
use App\Models\ProjectGrade;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GroupGradeRepository
{
    public function getGradeByGroup(int $groupId): ?ProjectGrade
    {
        return ProjectGrade::where('group_id', $groupId)->first();
    }

    public function isSupervisorForCommittee(int $doctorId, int $committeeId): bool
    {
        return InterviewCommittee::query()
            ->where('id', $committeeId)
            ->where('supervisor_id', $doctorId)
            ->exists();
    }

    public function updateGrade(ProjectGrade $grade , array $data): ProjectGrade
    {
        $grade->previous_total_grade = $grade->total_grade;
        $grade->presentation_grade   = $data['presentation_grade'] ?? $grade->presentation_grade;
        $grade->project_grade        = $data['project_grade'] ?? $grade->project_grade;
        $grade->total_grade          = $data['total_grade'] ?? $grade->total_grade;
        $grade->is_edited            = true;
        $grade->save();

        return $grade ;
    }

    public function createGrade(int $committeeId , array $data): ProjectGrade
    {
        return DB::transaction(function () use ($committeeId, $data) {

            $grade = ProjectGrade::create([
                'committee_id'       => $committeeId,
                'group_id'           => $data['group_id'],
                'presentation_grade' => $data['presentation_grade'],
                'project_grade'      => $data['project_grade'],
                'total_grade'        => $data['total_grade'],
            ]);

            if(!empty($data['exceptions']))
            {
                foreach ($data['exceptions'] as $studentId) {
                    GradeException::create([
                        'grade_id'   => $grade->id,
                        'student_id' => $studentId
                    ]);
                }
            }

            return $grade;
        });

    }

    public function getGradesForLastThreeYears(): \Illuminate\Database\Eloquent\Collection|array
    {
        $since = now()->startOfYear()->subYears(2);

        return ProjectGrade::query()
            ->where('created_at', '>=', $since)
            ->with([
                // المجموعة
                'group:id,name',

                // علاقات المجموعة (مفرد)
                'group.projectForm:id,group_id,arabic_title',
                'group.interviewSchedule:id,group_id,interview_date',

                // اللجنة + علاقاتها
                'committee:id,supervisor_id,member_id',
                'committee.adminSupervisor:id,name',
                'committee.adminSupervisor.profile:id,user_id,profile_image',
                'committee.adminMember:id,name',
                'committee.adminMember.profile:id,user_id,profile_image',
            ])
            ->get()
            ->groupBy(fn ($g) => (int) $g->created_at->format('Y'));
    }

    public function getGradesWithRelationsForYear(): Collection|array
    {
        $currentYear = now()->year;

        return ProjectGrade::with([
                'group.members.user',
                'GradeExceptions.user'
            ])
            ->whereYear('created_at', $currentYear)
            ->orderBy('group_id')
            ->get();
    }
}
