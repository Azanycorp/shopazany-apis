<?php

namespace App\Exceptions;

use Exception;

class PromoValidationException extends Exception
{
    public function __construct(string $message, int $httpStatus)
    {
        parent::__construct($message, $httpStatus);
    }

    public function getHttpStatus(): int
    {
        return $this->getCode();
    }
}
