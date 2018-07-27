<?php

namespace Rareloop\Lumberjack\QueryBuilder;

use Rareloop\Lumberjack\Helpers;
use Rareloop\Lumberjack\QueryBuilder\Exceptions\CannotRedeclarePostTypeOnQueryException;
use Rareloop\Lumberjack\QueryBuilder\QueryBuilder;
use Rareloop\Lumberjack\QueryBuilder\Contracts\QueryBuilder as QueryBuilderContract;

class ScopedQueryBuilder
{
    public function __construct($postClass)
    {
        $this->postClass = $postClass;

        $this->queryBuilder = Helpers::app(QueryBuilderContract::class);
    }

    public function __call($name, $arguments)
    {
        // Proxy QueryBuilder methods
        if (method_exists($this->queryBuilder, $name)) {
            $response = call_user_func_array([$this->queryBuilder, $name], $arguments);

            // We want to make sure that chaining continues through this proxy object so we
            // must only return the response of the QueryBuilder if it isn't the QueryBuilder itself
            return $response === $this->queryBuilder ? $this : $response;
        }

        // See if this is a scope function that needs calling
        $scopeFunctionName = 'scope' . ucfirst($name);

        $post = new $this->postClass(false, true);

        if (!method_exists($post, $scopeFunctionName)) {
            trigger_error('Call to undefined method '.$this->postClass.'::'.$scopeFunctionName.'()', E_USER_ERROR);
        }

        return $post->{$scopeFunctionName}($this);
    }

    public function getParameters()
    {
        return array_merge(
            $this->queryBuilder->getParameters(),
            ['post_type' => call_user_func([$this->postClass, 'getPostType'])]
        );
    }

    public function wherePostType($postType)
    {
        throw new CannotRedeclarePostTypeOnQueryException;
    }

    public function get()
    {
        return $this->postClass::query($this->getParameters());
    }
}
