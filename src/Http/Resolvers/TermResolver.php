<?php

namespace Rareloop\Lumberjack\Http\Resolvers;

use Rareloop\Lumberjack\Exceptions\MismatchedContextException;
use Rareloop\Lumberjack\Exceptions\MissingContextException;
use Rareloop\Lumberjack\Term;
use ReflectionParameter;
use Timber\Term as TimberTerm;
use Timber\Timber;

class TermResolver extends AbstractContextResolver
{
    protected function canResolve(ReflectionParameter $parameter): bool
    {
        $type = $parameter->getType();

        if (!$type || $type->isBuiltin()) {
            return false;
        }

        $className = $type->getName();

        return $className === Term::class
            || is_subclass_of($className, Term::class)
            || $className === TimberTerm::class
            || is_subclass_of($className, TimberTerm::class);
    }

    protected function resolve(ReflectionParameter $parameter): mixed
    {
        $className = $parameter->getType()->getName();
        $queriedObject = get_queried_object();

        if (!$queriedObject instanceof \WP_Term) {
            throw MissingContextException::forType($className, $queriedObject);
        }

        $term = Timber::get_term($queriedObject);

        if (!$term instanceof $className) {
            throw MismatchedContextException::forIncorrectClass($className, $term);
        }

        return $term;
    }
}
