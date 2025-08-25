<?php

namespace App\Http\Requests;

use App\Traits\ValidationFailedResponse;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePictureRequest extends FormRequest
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
            'profile_image' => ['required' , 'file' , 'image', 'mimes:jpeg,jpg,png', 'max:2048']
        ];
    }

}
