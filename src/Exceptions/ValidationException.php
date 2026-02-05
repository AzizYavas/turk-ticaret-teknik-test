<?php

namespace App\Exceptions;

use Exception;

class ValidationException extends Exception
{
    protected $message = 'Geçersiz veri';
    protected $code = 400;
    protected $errorCode = 'VALIDATION_ERROR';
    protected $errors = [];

    public function __construct(string $message = 'Geçersiz veri', array $errors = [], int $code = 400, string $errorCode = 'VALIDATION_ERROR')
    {
        $this->errors = $errors;
        $this->errorCode = $errorCode;
        parent::__construct($message, $code);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}

