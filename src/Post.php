<?php

namespace Rareloop\Lumberjack;

use Rareloop\Lumberjack\Exceptions\PostTypeRegistrationException;
use Timber\Post as TimberPost;

class Post extends TimberPost
{
    /**
     * Return the key used to register the post type with WordPress
     * First parameter of the `register_post_type` function:
     * https://codex.wordpress.org/Function_Reference/register_post_type
     *
     * @return string|null
     */
    public static function getPostType()
    {
        return null;
    }

    /**
     * Return the config to use to register the post type with WordPress
     * Second parameter of the `register_post_type` function:
     * https://codex.wordpress.org/Function_Reference/register_post_type
     *
     * @return array|null
     */
    protected static function getPostTypeConfig()
    {
        return null;
    }

    public static function register()
    {
        $postType = static::getPostType();
        $config = static::getPostTypeConfig();

        if (empty($postType)) {
            throw new PostTypeRegistrationException('Post type not set');
        }

        if (empty($config)) {
            throw new PostTypeRegistrationException('Config not set');
        }

        register_post_type($postType, $config);
    }
}
