<?php


namespace App\Http\Requests;

use App\Enums\ProfileStudentSpeciality;
use App\Rules\AllowedEmailDomain;
use App\Traits\ValidationFailedResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password as password_rule;

class RegisterStudentRequest extends FormRequest
{
    use ValidationFailedResponse;

    protected $stopOnFirstFailure = true;

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
            'email' => ['required' , 'email' , 'unique:users,email' , new AllowedEmailDomain() ],
            'university_id' => 'required|exists:users,university_number',
            'password' => ['required', password_rule::min(6)->numbers()->letters()],
            'student_speciality' => ['required', new Enum(ProfileStudentSpeciality::class)],
        ];
    }

}
