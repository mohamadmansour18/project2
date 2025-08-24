<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'image'             => $this->image,
            'number_of_members' => $this->number_of_members,
            'project_form_1'    => $this->projectForm
                ? '# form1'
                : null,
            'project_form_2'    => $this->projectForm2
                ? "# form2"
                : null,
        ];
    }
}
