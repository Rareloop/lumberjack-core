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
        return collect($reflection->getParameters())
            ->reject(fn($p) => Arr::has($resolvedParameters, $p->getPosition()))
            ->filter(fn($p) => $this->canResolve($p))
            ->reduce(function ($resolved, $p) {
                try {
                    $resolved[$p->getPosition()] = $this->resolve($p);
                } catch (MissingContextException $e) {
                    // If the context is entirely missing, we allow null if the typehint supports it
                    if (!$p->allowsNull()) {
                        throw $e;
                    }

                    $resolved[$p->getPosition()] = null;
                }

                return $resolved;
            }, $resolvedParameters);
    }

    abstract protected function canResolve(ReflectionParameter $parameter): bool;

    abstract protected function resolve(ReflectionParameter $parameter): mixed;
}
