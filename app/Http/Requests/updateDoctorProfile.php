<?php

namespace App\Http\Requests;

use App\Enums\ProfileGovernorate;
use App\Traits\ValidationFailedResponse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class updateDoctorProfile extends FormRequest
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
            'phone_number' => ['nullable' , 'regex:/^09[0-9]{8}$/'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'governorate' => ['nullable', new Enum(ProfileGovernorate::class)],
        ];
    }

}
