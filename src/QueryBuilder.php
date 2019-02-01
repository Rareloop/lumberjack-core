<?php

namespace Rareloop\Lumberjack;

use Rareloop\Lumberjack\Contracts\QueryBuilder as QueryBuilderContract;
use Rareloop\Lumberjack\Exceptions\InvalidMetaRelationshipException;
use Rareloop\Lumberjack\Post;
use Tightenco\Collect\Support\Collection;
use Timber\Timber;

class QueryBuilder implements QueryBuilderContract
{
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
        $params = [
            'post_type' => $this->postType,
        ];

        if (isset($this->limit)) {
            $params['posts_per_page'] = $this->limit;
        }

        if (isset($this->offset)) {
            $params['offset'] = $this->offset;
        }

        if (isset($this->orderBy)) {
            $params['orderby'] = $this->orderBy;
            $params['order'] = $this->order;
        }

        if (isset($this->metaOrderBy)) {
            $params['orderby'] = $this->metaOrderNumeric ? 'meta_value_num' : 'meta_value';
            $params['order'] = $this->metaOrder;
            $params['meta_key'] = $this->metaOrderBy;
        }

        if (!empty($this->whereIn)) {
            $params['post__in'] = $this->whereIn;
        }

        if (!empty($this->whereNotIn)) {
            $params['post__not_in'] = $this->whereNotIn;
        }

        if (isset($this->whereStatus)) {
            $params['post_status'] = $this->whereStatus;
        }

        if (!empty($this->metaQueries)) {
            $params['meta_query'] = [];

            if (isset($this->metaRelationship)) {
                $params['meta_query']['relation'] = $this->metaRelationship;
            }

            foreach ($this->metaQueries as $query) {
                $params['meta_query'][] = $query;
            }
        }

        return $params;
    }

    public function wherePostType($postType) : QueryBuilderContract
    {
        $this->postType = $postType;

        return $this;
    }

    public function limit($limit) : QueryBuilderContract
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset($offset) : QueryBuilderContract
    {
        $this->offset = $offset;

        return $this;
    }

    public function orderBy($orderBy, string $order = QueryBuilder::ASC) : QueryBuilderContract
    {
        $order = strtoupper($order);

        $this->orderBy = $orderBy;
        $this->order = $order;

        return $this;
    }

    public function orderByMeta($metaKey, string $order = QueryBuilder::ASC, string $type = null) : QueryBuilderContract
    {
        $order = strtoupper($order);

        $this->metaOrderBy = $metaKey;
        $this->metaOrder = $order;
        $this->metaOrderNumeric = ($type === QueryBuilder::NUMERIC ? true : false);

        return $this;
    }

    public function whereIdIn(array $ids) : QueryBuilderContract
    {
        $this->whereIn = $ids;

        return $this;
    }

    public function whereIdNotIn(array $ids) : QueryBuilderContract
    {
        $this->whereNotIn = $ids;

        return $this;
    }

    public function whereStatus() : QueryBuilderContract
    {
        $args = func_get_args();

        if (count($args) === 0) {
            throw new \InvalidArgumentException('`whereStatus` must be called with at least one argument');
        }

        $this->whereStatus = count($args) > 1 ? $args : $args[0];

        return $this;
    }

    public function whereMeta($key, $value, $compare = '=', $type = null) : QueryBuilderContract
    {
        $params = [
            'key' => $key,
            'value' => $value,
            'compare' => $compare,
        ];

        if ($type) {
            $params['type'] = $type;
        }

        $this->metaQueries[] = $params;

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

        $this->metaRelationship = $relation;

        return $this;
    }

    public function as($postClass) : QueryBuilderContract
    {
        $this->postClass = $postClass;

        return $this;
    }

    public function get() : Collection
    {
        return collect(Timber::get_posts($this->getParameters(), $this->postClass));
    }

    public function clone() : QueryBuilderContract
    {
        $clone = clone $this;

        return $clone;
    }
}
