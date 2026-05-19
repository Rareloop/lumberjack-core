<?php

namespace Rareloop\Lumberjack\Http\Resolvers;

use Rareloop\Lumberjack\Post;
use Timber\CoreEntityInterface;
use Timber\Post as TimberPost;
use Timber\Timber;

class PostResolver extends AbstractContextResolver
{
    protected function canResolveClass(string $className): bool
    {
        return is_a($className, Post::class, true)
            || is_a($className, TimberPost::class, true);
    }

    protected function resolveObject(string $className, mixed $context): mixed
    {
        return Timber::get_post($context);
    }
}
