<?php

namespace Rareloop\Lumberjack\Http\Resolvers;

use Rareloop\Lumberjack\Exceptions\MismatchedContextException;
use Rareloop\Lumberjack\Exceptions\MissingContextException;
use Rareloop\Lumberjack\User;
use ReflectionParameter;
use Timber\User as TimberUser;
use Timber\Timber;

class UserResolver extends AbstractContextResolver
{
    protected function canResolve(ReflectionParameter $parameter): bool
    {
        $type = $parameter->getType();

        if (!$type || $type->isBuiltin()) {
            return false;
        }

        $className = $type->getName();

        return $className === User::class
            || is_subclass_of($className, User::class)
            || $className === TimberUser::class;
    }

    protected function resolve(ReflectionParameter $parameter): mixed
    {
        $className = $parameter->getType()->getName();
        $queriedObject = get_queried_object();

        if (!$queriedObject instanceof \WP_User) {
            throw MissingContextException::forType($className, $queriedObject);
        }

        $user = Timber::get_user($queriedObject);

        if (!$user instanceof $className) {
            throw MismatchedContextException::forIncorrectClass($className, $user);
        }

        return $user;
    }
}
