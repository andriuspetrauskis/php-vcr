<?php

namespace VCR;

use Assert\InvalidArgumentException;

class VCRException extends InvalidArgumentException
{
    public const LIBRARY_HOOK_DISABLED = 500;
    public const REQUEST_ERROR = 600;

    public function __construct($message, $code, $propertyPath = null, $value = null, array $constraints = [])
    {
        parent::__construct($message, $code, $propertyPath, $value, $constraints);
    }
}
