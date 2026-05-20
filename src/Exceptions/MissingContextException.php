<?php

namespace Rareloop\Lumberjack\Exceptions;

class MissingContextException extends UnresolvableContextException
{
    public static function forType(string $expectedClass, mixed $actualObject): self
    {
        $actualType = get_debug_type($actualObject);

        return new static(
            "Could not resolve context for typehint [{$expectedClass}]. " .
            "The current WordPress queried object is [{$actualType}]."
        );
    }
}
