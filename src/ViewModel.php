<?php

namespace Rareloop\Lumberjack;

use Rareloop\Lumberjack\Contracts\Arrayable;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

abstract class ViewModel implements Arrayable
{
    public function toArray() : array
    {
        $propertyKeyValues = collect($this->validPropertyNames())
            ->mapWithKeys(function ($method) {
                return [
                    $method => $this->{$method},
                ];
            })
            ->toArray();

        $methodKeyValues = collect($this->validMethodNames())
            ->whereNotIn(null, $this->ignoredMethods())
            ->mapWithKeys(function ($method) {
                return [
                    $method => call_user_func([$this, $method]),
                ];
            })
            ->toArray();

        return array_merge($propertyKeyValues, $methodKeyValues);
    }

    protected function validMethodNames() : array
    {
        $class = new ReflectionClass(static::class);
        return collect($class->getMethods(ReflectionMethod::IS_PUBLIC))
            ->reject(function ($method) {
                return $method->isStatic() || $method->getNumberOfParameters() > 0;
            })
            ->map(function ($method) {
                return $method->getName();
            })
            ->all();
    }

    protected function validPropertyNames() : array
    {
        $class = new ReflectionClass(static::class);

        return collect($class->getProperties(ReflectionProperty::IS_PUBLIC))
            ->reject(function ($property) {
                return $property->isStatic();
            })
            ->map(function ($property) {
                return $property->getName();
            })
            ->all();
    }

    protected function ignoredMethods() : array
    {
        return [
            'toArray',
        ];
    }
}
