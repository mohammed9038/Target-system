<?php

namespace App\Exceptions;

use Exception;

class TargetValidationException extends Exception
{
    protected $errors;

    public function __construct(array $errors = [], $message = 'Target validation failed')
    {
        $this->errors = $errors;
        parent::__construct($message);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
