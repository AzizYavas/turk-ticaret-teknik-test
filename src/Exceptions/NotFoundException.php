<?php

namespace App\Exceptions;

use Exception;

class NotFoundException extends Exception
{
    protected $message = 'Kayıt bulunamadı';
    protected $code = 404;
    protected $errorCode = 'NOT_FOUND';

    public function __construct(string $message = 'Kayıt bulunamadı', int $code = 404, string $errorCode = 'NOT_FOUND')
    {
        $this->errorCode = $errorCode;
        parent::__construct($message, $code);
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}

