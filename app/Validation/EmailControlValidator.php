<?php

namespace App\Validation;

use Illuminate\Validation\Validator;

/** Impedisce CRLF negli indirizzi senza sostituire le regole RFC/DNS del chiamante. */
class EmailControlValidator extends Validator
{
    public function validateEmail($attribute, $value, $parameters)
    {
        if ((is_string($value) || (is_object($value) && method_exists($value, '__toString')))
            && strpbrk((string) $value, "\r\n") !== false) {
            return false;
        }

        return parent::validateEmail($attribute, $value, $parameters);
    }
}
