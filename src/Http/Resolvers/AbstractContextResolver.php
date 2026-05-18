<?php

namespace Rareloop\Lumberjack\Http\Resolvers;

use Illuminate\Support\Arr;
use Invoker\ParameterResolver\ParameterResolver;
use Rareloop\Lumberjack\Exceptions\MissingContextException;
use Rareloop\Lumberjack\Exceptions\UnresolvableContextException;
use ReflectionFunctionAbstract;
use ReflectionParameter;

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

            if (!$this->canResolve($parameter)) {
                continue;
            }

            try {
                $resolvedParameters[$parameter->getPosition()] = $this->resolve($parameter);
            } catch (MissingContextException $e) {
                // If the context is entirely missing, we allow null if the typehint supports it
                if (!$parameter->allowsNull()) {
                    throw $e;
                }

                $resolvedParameters[$parameter->getPosition()] = null;
            }
        }

        return $resolvedParameters;
    }

    abstract protected function canResolve(ReflectionParameter $parameter): bool;

    abstract protected function resolve(ReflectionParameter $parameter): mixed;
}
