<?php

namespace App\Http\Requests;

use App\Enums\AnnouncementAudience;
use App\Enums\AnnouncementType;
use App\Traits\ValidationFailedResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreAnnouncementRequest extends FormRequest
{
    use ValidationFailedResponse ;

    protected $stopOnFirstFailure = true ;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', new Enum(AnnouncementType::class)],
            'audience' => ['required', new Enum(AnnouncementAudience::class)],
            'attachment_path' => ['required', 'file'],
        ];
    }

}
