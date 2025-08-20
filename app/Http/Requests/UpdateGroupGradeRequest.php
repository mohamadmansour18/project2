<?php

namespace App\Http\Requests;

use App\Traits\ValidationFailedResponse;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupGradeRequest extends FormRequest
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
            'group_id' => 'required|exists:groups,id',
            'presentation_grade' => 'nullable|numeric|min:0|max:20',
            'project_grade' => 'nullable|numeric|min:0|max:80',
            'total_grade' => 'nullable|numeric|min:0|max:100',
        ];
    }

}
