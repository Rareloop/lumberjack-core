<?php

namespace Rareloop\Lumberjack\Http\Resolvers;

use Rareloop\Lumberjack\User;
use Timber\User as TimberUser;
use Timber\Timber;
use Timber\CoreEntityInterface;
use WP_User;

class UserResolver extends AbstractContextResolver
{
    protected function canResolveClass(string $className): bool
    {
        return is_a($className, User::class, true) || is_a($className, TimberUser::class, true);
    }

    protected function isValidContext(mixed $context, string $className): bool
    {
        return is_a($context, WP_User::class) || is_a($context, CoreEntityInterface::class);
    }

    protected function resolveObject(string $className, mixed $context): mixed
    {
        return Timber::get_user($context);
    }
}
