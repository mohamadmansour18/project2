<?php

namespace App\Http\Requests;

use App\Traits\ValidationFailedResponse;
use Illuminate\Foundation\Http\FormRequest;

class CreateInterviewCommitteeRequest extends FormRequest
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
            'doctor1_id'     => 'required|exists:users,id',
            'doctor2_id'     => 'required|different:doctor1_id|exists:users,id',
            'supervisor_id'  => 'required|exists:users,id',
        ];
    }

}
