<?php

namespace App\Http\Resources;

use App\Helpers\UrlHelper;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' =>$this->id,
            'title' => $this->title,
            'audience' => $this->audience->value,
            'created_at' => $this->created_at->format('Y-m-d'),
            'attachment_path' => UrlHelper::imageUrl($this->attachment_path),
        ];
    }
}
