<?php

namespace App\Repositories;

use App\Models\GradeException;
use App\Models\InterviewCommittee;
use App\Models\ProjectGrade;
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

    public function updateGrade(ProjectGrade $grade , array $data): void
    {
        $grade->previous_total_grade = $grade->total_grade;
        $grade->presentation_grade   = $data['presentation_grade'] ?? $grade->presentation_grade;
        $grade->project_grade        = $data['project_grade'] ?? $grade->project_grade;
        $grade->total_grade          = $data['total_grade'] ?? $grade->total_grade;
        $grade->is_edited            = true;
        $grade->save();

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
}
