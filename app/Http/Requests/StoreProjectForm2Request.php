<?php

namespace App\Http\Requests;

use App\Traits\ValidationFailedResponse;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectForm2Request extends FormRequest
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
            'group_id'             => 'required|integer|exists:groups,id',
            'arabic_title' => 'required|string|max:255',
            'user_segment'         => 'required|string',
            'development_procedure'=> 'required|string',
            'libraries_and_tools'  => 'required|string',
            'roadmap_file'   => 'required|file|mimetypes:application/pdf|max:10240',
            'work_plan_file' => 'required|file|mimetypes:application/pdf|max:10240',

        ];
    }

}
