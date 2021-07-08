<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Rules\Password;

trait PasswordValidationRules
{
    /**
     * Get the validation rules used to validate passwords.
     *
     * @return array
     * Edited SDS, do it here rather than in the vendor files
     */
    protected function passwordRules()
    {
        return [
            'required', 
            'string', 
            (new Password)->requireUppercase()->length(8)->requireNumeric()->requireSpecialCharacter(), 
            'confirmed'
        ];
    }
}
