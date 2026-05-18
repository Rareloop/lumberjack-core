<?php

namespace Rareloop\Lumberjack\Http\Resolvers;

use Rareloop\Lumberjack\Exceptions\MismatchedContextException;
use Rareloop\Lumberjack\Exceptions\MissingContextException;
use Rareloop\Lumberjack\PostType;
use ReflectionParameter;
use Timber\PostType as TimberPostType;
use WP_Post;
use WP_Post_Type;

class PostTypeResolver extends AbstractContextResolver
{
    protected function canResolve(ReflectionParameter $parameter): bool
    {
        $type = $parameter->getType();

        if (!$type || $type->isBuiltin()) {
            return false;
        }

        $className = $type->getName();

        return $className === PostType::class || $className === TimberPostType::class;
    }

    protected function resolve(ReflectionParameter $parameter): mixed
    {
        $className = $parameter->getType()->getName();
        $queriedObject = get_queried_object();

        $postType = null;

        if ($queriedObject instanceof WP_Post_Type) {
            $postType = new PostType($queriedObject->name);
        }

        if ($queriedObject instanceof WP_Post) {
            $postTypeObject = get_post_type_object($queriedObject->post_type);

            if ($postTypeObject) {
                $postType = new PostType($postTypeObject->name);
            }
        }

        if (!$postType) {
            throw MissingContextException::forType($className, $queriedObject);
        }

        if (!$postType instanceof $className) {
            throw MismatchedContextException::forIncorrectClass($className, $postType);
        }

        return $postType;
    }
}
