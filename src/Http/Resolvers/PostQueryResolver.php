<?php

namespace Rareloop\Lumberjack\Http\Resolvers;

use Rareloop\Lumberjack\Application;
use ReflectionParameter;
use Timber\PostQuery as TimberPostQuery;
use WP_Query;

class PostQueryResolver extends AbstractContextResolver
{
    public function __construct(protected Application $app)
    {
    }

    protected function canResolve(ReflectionParameter $parameter): bool
    {
        $type = $parameter->getType();

        if (!$type || $type->isBuiltin()) {
            return false;
        }

        $className = $type->getName();

        return $className === TimberPostQuery::class || is_subclass_of($className, TimberPostQuery::class);
    }

    protected function resolve(ReflectionParameter $parameter): mixed
    {
        $className = $parameter->getType()->getName();

        // Resolve WP_Query from the container instead of globals
        $query = $this->app->get(WP_Query::class);

        return new $className($query);
    }
}
