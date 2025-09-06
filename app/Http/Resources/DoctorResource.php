<?php

namespace App\Http\Resources;

use App\Enums\ProjectFormStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoctorResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // جلب ID المستخدم الحالي (الدكتور)
        $currentUserId = auth()->id();

        // تحقق إذا هذا الدكتور هو المشرف على أي استمارة
        $isSupervisor = $this->projectForms()
            ->where('user_id', $currentUserId)
            ->exists();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'profile_image' => $this->Profile?->profile_image,
            'is_supervisor_of_any_form' => $this->projectForms()->exists(),
            'pending_forms_count' => $this->projectForms()
                ->where('status', ProjectFormStatus::Pending)
                ->count(),
        ];
    }
}
