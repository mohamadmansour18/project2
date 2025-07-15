<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AllowedEmailDomain implements ValidationRule
{
    protected array $allowedEmailDomain = [
        'gmail.com' ,
        'hotmail.com' ,
        'yahoo.com' ,
        'outlook.com' ,
        'icloud.com' ,
    ];
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $domain = substr(strrchr($value , '@') , 1);

        if(!in_array( strtolower($domain) , $this->allowedEmailDomain , true))
        {
            $fail( implode(' , ' , $this->allowedEmailDomain) . ' : يجب أن يكون البريد الإلكتروني من أحد النطاقات المسموحة' );
        }
    }
}
