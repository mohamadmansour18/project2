<?php

namespace App\Http\Requests;

use App\Enums\ProfileGovernorate;
use App\Rules\AllowedEmailDomain;
use App\Traits\ValidationFailedResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password as password_rule;

class DoctorRegisterRequest extends FormRequest
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
            'email' => ['required' , 'email' , 'exists:users,email' , new AllowedEmailDomain()] ,
            'governorate' => ['required' , new Enum(ProfileGovernorate::class)],
            'password' => ['required' , password_rule::min(6)->numbers()->letters()]
        ];
    }

}
