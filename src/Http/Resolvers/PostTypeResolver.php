<?php

namespace Rareloop\Lumberjack\Http\Resolvers;

use Rareloop\Lumberjack\PostType;
use Timber\PostType as TimberPostType;

class PostTypeResolver extends AbstractContextResolver
{
    protected function canResolveClass(string $className): bool
    {
        return is_a($className, PostType::class, true) || is_a($className, TimberPostType::class, true);
    }

    protected function resolveObject(string $className, mixed $context): mixed
    {
        if (is_a($context, 'WP_Post_Type')) {
            return new PostType($context->name);
        }

        if (is_a($context, 'WP_Post') && function_exists('get_post_type_object')) {
            $postTypeObject = get_post_type_object($context->post_type);

            if ($postTypeObject) {
                return new PostType($postTypeObject->name);
            }
        }

        return null;
    }
}
