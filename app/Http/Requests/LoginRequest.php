<?php

namespace App\Http\Requests;

use App\Rules\AllowedEmailDomain;
use App\Traits\ValidationFailedResponse;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'password' => 'required',
            'fcm_token' => 'nullable|string' ,
        ];
    }

}
