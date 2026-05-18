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

        return is_a($className, PostType::class, true) || is_a($className, TimberPostType::class, true);
    }

    protected function resolve(ReflectionParameter $parameter): mixed
    {
        $className = $parameter->getType()->getName();
        $queriedObject = get_queried_object();

        $postType = $this->getPostTypeFromQueriedObject($queriedObject);

        if (!$postType) {
            throw MissingContextException::forType($className, $queriedObject);
        }

        if (!$postType instanceof $className) {
            throw MismatchedContextException::forIncorrectClass($className, $postType);
        }

        return $postType;
    }

    private function getPostTypeFromQueriedObject($queriedObject): ?PostType
    {
        if (is_a($queriedObject, 'WP_Post_Type')) {
            return new PostType($queriedObject->name);
        }

        if (is_a($queriedObject, 'WP_Post')) {
            if (function_exists('get_post_type_object')) {
                $postTypeObject = get_post_type_object($queriedObject->post_type);

                if ($postTypeObject) {
                    return new PostType($postTypeObject->name);
                }
            }
        }

        return null;
    }
}
