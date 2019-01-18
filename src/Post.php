<?php

namespace Rareloop\Lumberjack;

use Rareloop\Lumberjack\Exceptions\PostTypeRegistrationException;
use Rareloop\Lumberjack\ScopedQueryBuilder;
use Spatie\Macroable\Macroable;
use Timber\Post as TimberPost;
use Timber\Timber;

class Post extends TimberPost
{
    use Macroable {
        Macroable::__call as __macroableCall;
        Macroable::__callStatic as __macroableCallStatic;
    }

    public function __construct($id = false, $preventTimberInit = false)
    {
        /**
         * There are occasions where we do not want the bootstrap the data. At the moment this is
         * designed to make Query Scopes possible
         */
        if (!$preventTimberInit) {
            parent::__construct($id);
        }
    }

    public function __call($name, $arguments)
    {
        if (static::hasMacro($name)) {
            return $this->__macroableCall($name, $arguments);
        }

        return parent::__call($name, $arguments);
    }

    public static function __callStatic($name, $arguments)
    {
        if (static::hasMacro($name)) {
            return static::__macroableCallStatic($name, $arguments);
        }

        if (in_array($name, ['whereStatus', 'whereIdIn', 'whereIdNotIn'])) {
            $builder = static::builder();
            return call_user_func_array([$builder, $name], $arguments);
        }

        trigger_error('Call to undefined method '.__CLASS__.'::'.$name.'()', E_USER_ERROR);
    }

    /**
     * Create a QueryBuilder scoped to this Post type
     *
     * @return QueryBuilder
     */
    public static function builder() : ScopedQueryBuilder
    {
        return new ScopedQueryBuilder(static::class);
    }

    /**
     * Return the key used to register the post type with WordPress
     * First parameter of the `register_post_type` function:
     * https://codex.wordpress.org/Function_Reference/register_post_type
     *
     * @return string
     */
    public static function getPostType()
    {
        return 'post';
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

    /**
     * Register this PostType with WordPress
     *
     * @return void
     */
    public static function register()
    {
        $postType = static::getPostType();
        $config = static::getPostTypeConfig();

        if (empty($postType) || $postType === 'post') {
            throw new PostTypeRegistrationException('Post type not set');
        }

        if (empty($config)) {
            throw new PostTypeRegistrationException('Config not set');
        }

        register_post_type($postType, $config);
    }

    /**
     * Get all posts of this type
     *
     * @param  integer $perPage The number of items to return (defaults to all)
     * @return Illuminate\Support\Collection
     */
    public static function all($perPage = -1, $orderby = 'menu_order', $order = 'ASC')
    {
        $order = strtoupper($order);

        $args = [
            'posts_per_page' => $perPage,
            'orderby'       => $orderby,
            'order'         => $order,
        ];

        return static::query($args);
    }


    /**
     * Convenience function that takes a standard set of WP_Query arguments but mixes it with
     * arguments that mean we're selecting the right post type
     *
     * @param  array $args standard WP_Query array
     * @return Illuminate\Support\Collection
     */
    public static function query($args = null)
    {
        $args = is_array($args) ? $args : [];

        // Set the correct post type
        $args = array_merge($args, ['post_type' => static::getPostType()]);

        if (!isset($args['post_status'])) {
            $args['post_status'] = 'publish';
        }

        return static::posts($args);
    }

    /**
     * Raw query function that uses the arguments provided to make a call to Timber::get_posts
     * and casts the returning data in instances of ourself.
     *
     * @param  array $args standard WP_Query array
     * @return Illuminate\Support\Collection
     */
    private static function posts($args = null)
    {
        return collect(Timber::get_posts($args, get_called_class()));
    }
}
