<?php

namespace App\Http\Requests;

use App\Enums\GroupSpecialityNeeded;
use App\Enums\GroupType;
use App\Traits\ValidationFailedResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateGroupRequest extends FormRequest
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
        $groupId = $this->route('group')->id ?? null;

        return [
            'name' => [
                'required',
                'string',
                Rule::unique('groups', 'name')->ignore($groupId),
            ],
            'description' => ['required', 'string'],
            'speciality_needed' => ['required', 'array'],
            'speciality_needed.*' => [new Enum(GroupSpecialityNeeded::class)],
            'framework_needed' => ['nullable', 'array'],
            'framework_needed.*' => ['string'],
            'type' => ['required', new Enum(GroupType::class)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
        ];
    }

}
