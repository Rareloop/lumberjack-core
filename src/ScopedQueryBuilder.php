<?php

namespace Rareloop\Lumberjack;

use Error;
use Rareloop\Lumberjack\Contracts\QueryBuilder as QueryBuilderContract;
use Rareloop\Lumberjack\Exceptions\CannotRedeclarePostClassOnQueryException;
use Rareloop\Lumberjack\Exceptions\CannotRedeclarePostTypeOnQueryException;
use Rareloop\Lumberjack\Helpers;
use Rareloop\Lumberjack\QueryBuilder;
use ReflectionClass;
use ReflectionMethod;

class ScopedQueryBuilder
{
    protected $queryBuilder;

    public function __construct(protected $postClass)
    {
        $this->queryBuilder = Helpers::app(QueryBuilderContract::class);

        $this->queryBuilder
            ->wherePostType(call_user_func([$this->postClass, 'getPostType']));
    }

    public function __call($name, $arguments)
    {
        // Proxy QueryBuilder methods
        if ($this->hasQueryBuilderMethod($name)) {
            $response = call_user_func_array([$this->queryBuilder, $name], $arguments);

            // We want to make sure that chaining continues through this proxy object so we
            // must only return the response of the QueryBuilder if it isn't the QueryBuilder itself
            return $response === $this->queryBuilder ? $this : $response;
        }

        // See if this is a scope function that needs calling
        $scopeFunctionName = 'scope' . ucfirst((string) $name);

        $reflection = new ReflectionClass($this->postClass);
        $publicMethods = collect($reflection->getMethods(ReflectionMethod::IS_PUBLIC))->map(fn($method) => $method->getName())->toArray();

        if (!in_array($scopeFunctionName, $publicMethods)) {
            throw new Error('Call to undefined method ' . $this->postClass . '::' . $scopeFunctionName . '()');
        }

        array_unshift($arguments, $this);

        return new $this->postClass(false, true)->{$scopeFunctionName}(...$arguments);
    }

    /**
     * Does the QueryBuilder we're using have a method with the provided name? This will also
     * check any functionality added via a macro.
     *
     * @param  string  $name The method name
     * @return boolean
     */
    protected function hasQueryBuilderMethod(string $name): bool
    {
        if (method_exists($this->queryBuilder, $name)) {
            return true;
        }

        if (method_exists($this->queryBuilder, 'hasMacro') && $this->queryBuilder->hasMacro($name)) {
            return true;
        }

        return false;
    }

    public function wherePostType($postType): never
    {
        throw new CannotRedeclarePostTypeOnQueryException;
    }
}
