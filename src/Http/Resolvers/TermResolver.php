<?php

namespace Rareloop\Lumberjack\Http\Resolvers;

use Rareloop\Lumberjack\Term;
use Timber\Term as TimberTerm;
use Timber\Timber;

class TermResolver extends AbstractContextResolver
{
    protected function canResolveClass(string $className): bool
    {
        return is_a($className, Term::class, true) || is_a($className, TimberTerm::class, true);
    }

    protected function resolveObject(string $className, mixed $context): mixed
    {
        return Timber::get_term($context);
    }
}
