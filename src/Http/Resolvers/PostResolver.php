<?php

namespace Rareloop\Lumberjack\Http\Resolvers;

use Rareloop\Lumberjack\Post;
use Timber\Post as TimberPost;
use Timber\Timber;
use Timber\CoreEntityInterface;

class PostResolver extends AbstractContextResolver
{
    protected function canResolveClass(string $className): bool
    {
        return is_a($className, Post::class, true)
            || is_a($className, TimberPost::class, true);
    }

    protected function isValidContext(mixed $context, string $className): bool
    {
        return is_a($context, 'WP_Post') || is_a($context, CoreEntityInterface::class);
    }

    protected function resolveObject(string $className, mixed $context): mixed
    {
        return Timber::get_post($context);
    }
}
