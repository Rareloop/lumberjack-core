<?php

namespace Rareloop\Lumberjack\Http\Resolvers;

use Rareloop\Lumberjack\Application;
use Timber\PostQuery as TimberPostQuery;
use Timber\PostCollectionInterface;
use Timber\Timber;
use WP_Query;

class PostQueryResolver extends AbstractContextResolver
{
    public function __construct(protected Application $app)
    {
    }

    protected function canResolveClass(string $className): bool
    {
        return is_a($className, TimberPostQuery::class, true)
            || is_a($className, PostCollectionInterface::class, true);
    }

    protected function isValidContext(mixed $context, string $className): bool
    {
        return is_a($context, WP_Query::class);
    }

    protected function getContext(): mixed
    {
        return $this->app->get(WP_Query::class);
    }

    protected function resolveObject(string $className, mixed $context): mixed
    {
        // If they asked for the interface or the base Timber PostQuery, use the factory
        if ($className === PostCollectionInterface::class || $className === TimberPostQuery::class) {
            return Timber::get_posts($context);
        }

        // If it's a subclass (like Rareloop\Lumberjack\PostQuery), we must instantiate it manually
        // to ensure we get the correct instance type.
        return new $className($context);
    }
}
