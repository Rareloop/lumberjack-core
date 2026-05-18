<?php

namespace Rareloop\Lumberjack\Exceptions;

class MismatchedContextException extends UnresolvableContextException
{
    public static function forIncorrectClass(string $expectedClass, object $actualTimberObject): self
    {
        $actualClass = $actualTimberObject::class;

        return new static(
            "Resolved a WordPress object, but it was of type [{$actualClass}] " .
            "instead of the expected [{$expectedClass}]."
        );
    }
}
