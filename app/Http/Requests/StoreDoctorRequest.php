<?php

namespace App\Http\Requests;

use App\Rules\AllowedEmailDomain;
use App\Traits\ValidationFailedResponse;
use Illuminate\Foundation\Http\FormRequest;

class StoreDoctorRequest extends FormRequest
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
            'name' => ['required', 'string' , 'max:255'],
            'email' => ['required',  'email', 'max:255', 'unique:users,email' , new AllowedEmailDomain()],
            'profile_image' => ['nullable', 'file' , 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
        ];
    }

}
