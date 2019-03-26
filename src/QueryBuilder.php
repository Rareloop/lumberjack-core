<?php

namespace Rareloop\Lumberjack;

use Rareloop\Lumberjack\Contracts\QueryBuilder as QueryBuilderContract;
use Rareloop\Lumberjack\Exceptions\InvalidMetaRelationshipException;
use Rareloop\Lumberjack\Post;
use Spatie\Macroable\Macroable;
use Tightenco\Collect\Support\Collection;
use Timber\Timber;

class QueryBuilder implements QueryBuilderContract
{
    use Macroable;

    protected $postClass = Post::class;

    private $postType;

    private $limit;
    private $offset;

    private $orderby;
    private $order;

    private $metaOrderBy;
    private $metaOrder;
    private $metaOrderNumeric;

    private $whereIn;
    private $whereNotIn;

    private $metaRelationship;
    private $metaQueries = [];

    private $params = [];

    // Order Directions
    const DESC = 'DESC';
    const ASC = 'ASC';

    // Field Types
    const NUMERIC = 'numeric';

    // Logical Operators
    const OR = 'OR';
    const AND = 'AND';

    public function getParameters() : array
    {
        return $this->params;
    }

    public function wherePostType($postType) : QueryBuilderContract
    {
        $this->params['post_type'] = $postType;

        return $this;
    }

    public function limit($limit) : QueryBuilderContract
    {
        $this->params['posts_per_page'] = $limit;

        return $this;
    }

    public function offset($offset) : QueryBuilderContract
    {
        $this->params['offset'] = $offset;

        return $this;
    }

    public function orderBy($orderBy, string $order = QueryBuilder::ASC) : QueryBuilderContract
    {
        $order = strtoupper($order);

        $this->params['orderby'] = $orderBy;
        $this->params['order'] = $order;

        return $this;
    }

    public function orderByMeta($metaKey, string $order = QueryBuilder::ASC, string $type = null) : QueryBuilderContract
    {
        $order = strtoupper($order);

        $this->params['orderby'] = ($type === QueryBuilder::NUMERIC ? true : false) ? 'meta_value_num' : 'meta_value';
        $this->params['order'] = $order;
        $this->params['meta_key'] = $metaKey;

        return $this;
    }

    public function whereIdIn(array $ids) : QueryBuilderContract
    {
        $this->params['post__in'] = $ids;

        return $this;
    }

    public function whereIdNotIn(array $ids) : QueryBuilderContract
    {
        $this->params['post__not_in'] = $ids;

        return $this;
    }

    public function whereStatus() : QueryBuilderContract
    {
        $args = func_get_args();

        if (count($args) === 0) {
            throw new \InvalidArgumentException('`whereStatus` must be called with at least one argument');
        }

        $this->params['post_status'] = count($args) > 1 ? $args : $args[0];

        return $this;
    }

    protected function initialiseMetaQuery()
    {
        $this->params['meta_query'] = $this->params['meta_query'] ?? [];
    }

    public function whereMeta($key, $value, $compare = '=', $type = null) : QueryBuilderContract
    {
        $meta = [
            'key' => $key,
            'value' => $value,
            'compare' => $compare,
        ];

        if ($type) {
            $meta['type'] = $type;
        }

        $this->initialiseMetaQuery();
        $this->params['meta_query'][] = $meta;

        return $this;
    }

    public function whereMetaRelationshipIs(string $relation) : QueryBuilderContract
    {
        $relation = strtoupper($relation);

        if (!in_array($relation, [QueryBuilder::AND, QueryBuilder::OR])) {
            throw new InvalidMetaRelationshipException(
                '`whereMetaRelationshipIs` must be passed QueryBuilder::AND or QueryBuilder::OR'
            );
        }

        $this->initialiseMetaQuery();
        $this->params['meta_query']['relation'] = $relation;

        return $this;
    }

    public function as($postClass) : QueryBuilderContract
    {
        $this->postClass = $postClass;

        return $this;
    }

    public function get() : Collection
    {
        $posts = Timber::get_posts($this->getParameters(), $this->postClass);

        if (!is_array($posts)) {
            $posts = [];
        }

        return collect($posts);
    }

    /**
     * Get the first Post that matches the current query. If no Post matches then return `null`.
     *
     * @return Rareloop\Lumberjack\Post|null
     */
    public function first() : ?Post
    {
        $params = array_merge($this->getParameters(), [
            'limit' => 1,
        ]);

        $posts = Timber::get_posts($params, $this->postClass);

        if (!is_array($posts)) {
            return null;
        }

        return collect($posts)->first();
    }

    public function clone() : QueryBuilderContract
    {
        $clone = clone $this;

        return $clone;
    }
}
