<?php

namespace App\Http\Resources;

use App\Helpers\UrlHelper;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class GroupProjectResource extends JsonResource
{
    public function toArray($request)
    {
        $excluded = $this->projectGrade
            ? $this->projectGrade->GradeExceptions->pluck('student_id')->toArray()
            : [];

        // فلترة أعضاء الغروب
        $filteredMembers = $this->members->filter(function ($member) use ($excluded) {
            return !in_array($member->user_id, $excluded);
        });

        return [
            'group_id' => $this->id,
            'name' => $this->name,

            'form1' => $this->projectForm ? [
                "id"=>$this->projectForm->id,
                'title' => $this->projectForm->arabic_title,
                'supervisor' => $this->projectForm->users->name ?? null,
                'supervisor_profile_image' => $this->projectForm->users->Profile->profile_image ?? null,
                'submission_date' => $this->projectForm->submission_date
                    ? Carbon::parse($this->projectForm->submission_date)->format('Y-m-d')
                    : null,
                'status' => $this->projectForm->status,
                'signatures_count' => $this->projectForm->signatures->count(),
            ] : null,

            'form2' => $this->projectForm2 ? [
                "id"=>$this->projectForm2->id,
                'title' => $this->projectForm2->arabic_project_title,
                'submission_date' => $this->projectForm2->submission_date
                    ? Carbon::parse($this->projectForm2->submission_date)->format('Y-m-d')
                    : null,

                'status' => $this->projectForm2->status,
            ] : null,

            'grades' => $this->projectGrade ? [
                'presentation_grade' => $this->projectGrade->presentation_grade,
                'project_grade' => $this->projectGrade->project_grade,
                'total' => $this->projectGrade->total_grade,
                'committee' => [
                    'supervisor' => $this->projectGrade->committee->adminSupervisor->name ?? null,
                    'supervisor_profile_image' => UrlHelper::imageUrl($this->projectGrade->committee->adminSupervisor->Profile->profile_image),
                    'member' => $this->projectGrade->committee->adminMember->name ?? null,
                    'member_profile_image' => UrlHelper::imageUrl($this->projectGrade->committee->adminMember->Profile->profile_image),
                ]
            ] : null,

             'members' => $filteredMembers->values()->map(function ($member) {
                return [
                    'id' => $member->user->id,
                    'name' => $member->user->name,
                    'university_number' => $member->user->university_number,
                    'profile_image' => UrlHelper::imageUrl($member->user->Profile->profile_image) ,
                ];
            }),

            'final_interview' => $this->final_interview ? [
                'date' => $this->final_interview->interview_date
                    ? $this->final_interview->interview_date->format('Y-m-d')
                    : null,

                'start_time' => $this->final_interview->interview_time
                    ? $this->final_interview->interview_time->format('H:i')
                    : null,

                'end_time' => $this->final_interview->interview_end_time
                    ? $this->final_interview->interview_end_time->format('H:i')
                    : null,
            ] : null,



        ];
    }
}
