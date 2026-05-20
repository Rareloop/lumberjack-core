<?php

namespace Rareloop\Lumberjack\Exceptions;

class MismatchedContextException extends UnresolvableContextException
{
    public static function forIncorrectClass(string $expectedClass, mixed $actualValue): self
    {
        $actualType = is_object($actualValue) ? $actualValue::class : gettype($actualValue);

        return new static(
            "Resolved a WordPress object, but it was of type [{$actualType}] " .
            "instead of the expected [{$expectedClass}]."
        );
    }
}
