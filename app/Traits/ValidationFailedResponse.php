<?php

namespace App\Traits;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
trait ValidationFailedResponse
{
    public function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->first();

        throw new HttpResponseException(response()->json([
            'message' => $error,
            'status_code' => 422,
        ] , 422 ) );
    }
}
