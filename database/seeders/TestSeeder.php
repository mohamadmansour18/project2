<?php

namespace Database\Seeders;

use App\Enums\ProjectFormStatus;
use App\Models\InterviewCommittee;
use App\Models\InterviewSchedule;
use App\Models\ProjectForm;
use App\Models\ProjectGrade;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $supervisor_id = [1,2,2,3,2,5];
        $member_id =     [6,7,8,9,10,11];

        $committee_id = [1,2,2,4,2,6];
        $group_id_Schedules = [1,2,3,4,5,6];
        $interview_date = [
            '2025-09-01',
            '2025-09-02',
            '2025-09-03',
            '2025-09-04',
            '2025-09-05',
            '2025-09-06',
        ];

        $interview_time = [
            '09:00:00',
            '10:30:00',
            '11:00:00',
            '13:00:00',
            '14:30:00',
            '15:00:00',
        ];

        $interview_end_time = [
            '09:30:00',
            '11:00:00',
            '11:30:00',
            '13:30:00',
            '15:00:00',
            '15:30:00',
        ];

        $group_id_Form = [1,2,3,4,5,6];
        $user_id_Form = [10,11,12,13,14,15];


        DB::transaction(function () use (
            $supervisor_id, $member_id,
            $committee_id, $group_id_Schedules,
            $interview_date, $interview_time, $interview_end_time,
            $group_id_Form, $user_id_Form
        ){
            for ($i = 0 ; $i < count($supervisor_id) ; $i++)
            {
                InterviewCommittee::create([
                    'supervisor_id' => $supervisor_id[$i],
                    'member_id' => $member_id[$i]
                ]);

                InterviewSchedule::create([
                    'committee_id' => $committee_id[$i],
                    'group_id' => $group_id_Schedules[$i],
                    'interview_date' => $interview_date[$i],
                    'interview_time' => $interview_time[$i],
                    'interview_end_time' => $interview_end_time[$i],
                ]);

                ProjectForm::create([
                    'group_id' => $group_id_Form[$i],
                    'user_id' => $user_id_Form[$i],
                    'arabic_title' => 'test',
                    'english_title' => 'test',
                    'description' => 'test',
                    'project_scope' => 'test',
                    'targeted_sector' => 'test',
                    'sector_classification' => 'test',
                    'stakeholders' => 'test',
                    'supervisor_signature' => 'test',
                    'filled_form_file_path' => 'test.jpg',
                    'status' => ProjectFormStatus::Pending
                ]);
            }
                ProjectGrade::create([
                    'committee_id' => 2,
                    'group_id' => 2,
                    'presentation_grade' => 20,
                    'project_grade' => 60,
                    'total_grade' => 80,
                ]);
        });

    }
}
