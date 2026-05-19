<?php

namespace Rareloop\Lumberjack;

use Spatie\Macroable\Macroable;
use Timber\User as TimberUser;

class User extends TimberUser
{
    use Macroable;

    /**
     * Get the User class associated with a user object.
     *
     * @param \WP_User|null $user
     * @return string
     */
    public static function userClass(?\WP_User $user = null): string
    {
        return apply_filters('timber/user/class', User::class, $user);
    }
}
