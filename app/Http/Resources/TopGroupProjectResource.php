<?php

namespace App\Http\Resources;

use App\Helpers\UrlHelper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopGroupProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'group_id' => $this->id,
            'name' => $this->name,
            'group_image' => UrlHelper::imageUrl($this->image),
            'idea_title' => $this->projectForm->arabic_title
                ?? $this->projectForm2->arabic_project_title
                ?? null,
            'grades' => $this->projectGrade ? [
                'presentation_grade' => $this->projectGrade->presentation_grade,
                'project_grade' => $this->projectGrade->project_grade,
                'total' => $this->projectGrade->total_grade,
            ] : null,
            'members' => $this->members->map(function ($member) {
                return [
                    'id' => $member->user->id,
                    'name' => $member->user->name,
                    'university_number' => $member->user->university_number,
                    'profile_image' => UrlHelper::imageUrl($member->user->profile->profile_image ?? null),
                ];
            }),
        ];
    }
}
