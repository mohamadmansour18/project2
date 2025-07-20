<?php

namespace App\Http\Requests;

use App\Enums\GroupSpecialityNeeded;
use App\Enums\GroupType;
use App\Traits\ValidationFailedResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class CreateGroupRequest extends FormRequest
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
            'name' => ['required', 'string', 'unique:groups,name'],
            'description' => ['required', 'string'],
            'speciality_needed' => ['required', 'array'],
            'speciality_needed.*' => [new Enum(GroupSpecialityNeeded::class)],
            'framework_needed' => ['nullable','array'],
            'framework_needed.*' => ['string'],
            'type' => ['required', new Enum(GroupType::class)],
            'image' => ['nullable', 'image', 'mimes:jpg,png,jpeg', 'max:2048'],
            'invitations' => ['nullable', 'array'],
            'invitations.*' => ['exists:users,id'],
        ];
    }

}
