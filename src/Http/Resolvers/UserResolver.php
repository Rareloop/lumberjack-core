<?php

namespace Rareloop\Lumberjack\Http\Resolvers;

use Rareloop\Lumberjack\User;
use Timber\User as TimberUser;
use Timber\Timber;

class UserResolver extends AbstractContextResolver
{
    protected function canResolveClass(string $className): bool
    {
        return is_a($className, User::class, true) || is_a($className, TimberUser::class, true);
    }

    protected function resolveObject(string $className, mixed $context): mixed
    {
        return Timber::get_user($context);
    }
}
