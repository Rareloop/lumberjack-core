<?php

namespace Rareloop\Lumberjack\Http;

use Rareloop\Router\Route;
use Rareloop\Router\Router as RareRouter;

class Router extends RareRouter
{
    private $defaultControllerNamespace = 'App\Http\Controllers\\';

    /**
     * Map a router action to a set of Http verbs and a URI
     *
     * @param  array  $verbs
     * @param  string $uri
     * @param  callable|string $callback
     * @return Rareloop\Router\Route
     */
    public function map(array $verbs, string $uri, $callback): Route
    {
        if ($this->isControllerString($callback)) {
            $callback = $this->normaliseCallbackString($callback);
        }

        return parent::map($verbs, $uri, $callback);
    }

    /**
     * Is the provided callback action a Controller string
     *
     * @param  mixed  $callback
     * @return boolean
     */
    private function isControllerString($callback) : bool
    {
        return is_string($callback) && strpos($callback, '@') !== false;
    }

    /**
     * Add the default namespace to the Controller classname if required
     *
     * @param  string $callback
     * @return string
     */
    private function normaliseCallbackString(string $callback) : string
    {
        @list($controller, $method) = explode('@', $callback);

        if (class_exists($this->defaultControllerNamespace . $controller)) {
            return $this->defaultControllerNamespace . $callback;
        }

        return $callback;
    }
}
