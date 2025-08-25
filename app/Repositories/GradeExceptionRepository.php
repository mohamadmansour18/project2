<?php

namespace App\Repositories;

use App\Models\GradeException;

class GradeExceptionRepository
{
    public function getExceptionStudentIdsByGrade($gradeId)
    {
        return GradeException::where('grade_id', $gradeId)->pluck('student_id')->all();
    }
}
