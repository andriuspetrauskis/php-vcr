<?php

namespace VCR;

use Assert\InvalidArgumentException;

class VCRException extends InvalidArgumentException
{
    public const LIBRARY_HOOK_DISABLED = 500;
    public const REQUEST_ERROR = 600;

    /**
     * @inheritDoc
     * @param mixed $value
     * @param array<mixed> $constraints
     */
    public function __construct(string $message, int $code, string $propertyPath = null, $value = null, array $constraints = [])
    {
        parent::__construct($message, $code, $propertyPath, $value, $constraints);
    }
}
