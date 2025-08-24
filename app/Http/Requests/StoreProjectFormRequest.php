<?php

namespace App\Http\Requests;

use App\Traits\ValidationFailedResponse;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectFormRequest extends FormRequest
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
            'user_id' => ['required', 'exists:users,id'],
            'arabic_title' => ['required', 'string'],
            'english_title' => ['required', 'string'],
            'description' => ['required', 'string'],
            'project_scope' => ['required', 'string'],
            'targeted_sector' => ['required', 'string'],
            'sector_classification' => ['required', 'string'],
            'stakeholders' => ['required', 'string'],
        ];
    }

}
