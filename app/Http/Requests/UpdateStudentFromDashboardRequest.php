<?php

namespace App\Http\Requests;

use App\Enums\ProfileStudentStatus;
use App\Traits\ValidationFailedResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateStudentFromDashboardRequest extends FormRequest
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
            'university_number' => ['sometimes' , 'filled' , 'integer' , 'min:1' , 'unique:users,university_number'],
            'name' => ['sometimes', 'filled' , 'string' , 'max:255'],
            'student_status' =>['sometimes' , 'filled' , new Enum(ProfileStudentStatus::class)],
        ];
    }

}
