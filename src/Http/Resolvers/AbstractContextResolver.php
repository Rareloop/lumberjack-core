<?php

namespace Rareloop\Lumberjack\Http\Resolvers;

use Illuminate\Support\Arr;
use Invoker\ParameterResolver\ParameterResolver;
use Rareloop\Lumberjack\Exceptions\MismatchedContextException;
use Rareloop\Lumberjack\Exceptions\MissingContextException;
use ReflectionFunctionAbstract;

abstract class AbstractContextResolver implements ParameterResolver
{
    public function getParameters(
        ReflectionFunctionAbstract $reflection,
        array $providedParameters,
        array $resolvedParameters
    ): array {
        foreach ($reflection->getParameters() as $parameter) {
            if (Arr::has($resolvedParameters, $parameter->getPosition())) {
                continue;
            }

            $type = $parameter->getType();

            if (!$type || $type->isBuiltin()) {
                continue;
            }

            $className = $type->getName();

            if (!$this->canResolveClass($className)) {
                continue;
            }

            try {
                $context = $this->getContext();

                if (is_null($context)) {
                    throw MissingContextException::forType($className, $context);
                }

                if (!$this->isValidContext($context, $className)) {
                    throw MismatchedContextException::forIncorrectClass($className, $context);
                }

                $resolvedObject = $this->resolveObject($className, $context);

                if (!is_null($resolvedObject) && !$resolvedObject instanceof $className) {
                    throw MismatchedContextException::forIncorrectClass($className, $resolvedObject);
                }

                $resolvedParameters[$parameter->getPosition()] = $resolvedObject;
            } catch (MissingContextException | MismatchedContextException $e) {
                // If the context is entirely missing or mismatched, we allow null if the typehint supports it
                if (!$parameter->allowsNull()) {
                    throw $e;
                }

                $resolvedParameters[$parameter->getPosition()] = null;
            }
        }

        return $resolvedParameters;
    }

    /**
     * Get the raw context object to resolve from (e.g. WP_Post, WP_Term, WP_Query).
     * Defaults to the current WordPress queried object.
     *
     * @return mixed
     */
    protected function getContext(): mixed
    {
        return get_queried_object();
    }

    /**
     * Determine if this resolver can handle the given class type-hint.
     *
     * @param string $className
     * @return bool
     */
    abstract protected function canResolveClass(string $className): bool;

    /**
     * Determine if the current context is valid for this resolver.
     *
     * @param mixed $context
     * @param string $className
     * @return bool
     */
    abstract protected function isValidContext(mixed $context, string $className): bool;

    /**
     * Build the concrete object instance from the raw context.
     *
     * @param string $className
     * @param mixed $context
     * @return mixed
     */
    abstract protected function resolveObject(string $className, mixed $context): mixed;
}
