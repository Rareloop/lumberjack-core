<?php

namespace Rareloop\Lumberjack\Contracts;

use Tightenco\Collect\Support\Collection;

interface QueryBuilder
{
    public function getParameters() : array;

    public function wherePostType($postType) : QueryBuilder;

    public function limit($limit) : QueryBuilder;

    public function offset($offset) : QueryBuilder;

    public function orderBy($orderBy, string $order = QueryBuilder::ASC) : QueryBuilder;

    public function orderByMeta($metaKey, string $order = QueryBuilder::ASC, string $type = null) : QueryBuilder;

    public function whereIdIn(array $ids) : QueryBuilder;

    public function whereIdNotIn(array $ids) : QueryBuilder;

    public function whereStatus() : QueryBuilder;

    public function whereMeta($key, $value, $compare = '=', $type = null) : QueryBuilder;

    public function whereMetaRelationshipIs(string $relation) : QueryBuilder;

    public function get() : Collection;

    public function clone() : QueryBuilder;
}
