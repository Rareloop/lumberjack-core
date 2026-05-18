<?php

namespace Rareloop\Lumberjack\Http\Resolvers;

use Rareloop\Lumberjack\Exceptions\MismatchedContextException;
use Rareloop\Lumberjack\Exceptions\MissingContextException;
use ReflectionParameter;
use Timber\Post;
use Timber\Timber;

class PostResolver extends AbstractContextResolver
{
    protected function canResolve(ReflectionParameter $parameter): bool
    {
        $type = $parameter->getType();

        if (!$type || $type->isBuiltin()) {
            return false;
        }

        $className = $type->getName();

        return $className === Post::class || is_subclass_of($className, Post::class);
    }

    protected function resolve(ReflectionParameter $parameter): mixed
    {
        $className = $parameter->getType()->getName();
        $queriedObject = get_queried_object();

        if (!$queriedObject instanceof \WP_Post) {
            throw MissingContextException::forType($className, $queriedObject);
        }

        $post = Timber::get_post($queriedObject);

        if (!$post instanceof $className) {
            throw MismatchedContextException::forIncorrectClass($className, $post);
        }

        return $post;
    }
}
