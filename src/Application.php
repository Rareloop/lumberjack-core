<?php

namespace Rareloop\Lumberjack;

use Closure;
use DI\ContainerBuilder;
use Illuminate\Support\Collection;
use Interop\Container\ContainerInterface as InteropContainerInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Rareloop\Router\Invoker;
use function Http\Response\send;

class Application implements ContainerInterface, InteropContainerInterface
{
    private $container;
    private $loadedProviders = [];
    private $booted = false;
    private $basePath;
    private $requestHandled = false;

    private $nonSingletonClassBinds = [];
    private $allBinds = [];

    public function __construct($basePath = false)
    {
        $this->container = ContainerBuilder::buildDevContainer();

        $this->bind(Application::class, $this);

        $GLOBALS['__app__'] = $this;

        if ($basePath) {
            $this->setBasePath($basePath);
        }
    }

    public function setBasePath(string $basePath)
    {
        $this->basePath = $basePath;

        $this->bindPathsInContainer();
    }

    protected function bindPathsInContainer()
    {
        $this->bind('path.base', $this->basePath());
        $this->bind('path.config', $this->configPath());
    }

    public function basePath()
    {
        return $this->basePath;
    }

    public function configPath()
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'config';
    }

    public function bind($key, $value)
    {
        // Prevent PHP-DI from creating singletons from class binds or closure factories
        if ($this->isClassString($value) || is_callable($value)) {
            $this->nonSingletonClassBinds[] = $key;
        }

        $this->addToContainer($key, $value);
    }

    protected function isClassString($value)
    {
        return is_string($value) && class_exists($value);
    }

    protected function addToContainer($key, $value)
    {
        if ($this->isClassString($value)) {
            $value = \DI\autowire($value);
        }

        $this->container->set($key, $value);

        $this->allBinds[] = $key;
    }

    /**
     * Bind a singleton into the container
     *
     * Second parameter is either a class name or a closure factory.
     *
     * @param  String $key
     * @param  String|Closure $value
     * @return void
     */
    public function singleton($key, $value)
    {
        $this->addToContainer($key, $value);
    }

    /**
     * Always creates a new object instance, regardless of whether or not the $key has previously
     * been bound as a singleton.
     *
     * @param  String $key
     * @param  array  $params
     * @return mixed
     */
    public function make($key, array $params = [])
    {
        return $this->container->make($key, $params);
    }

    /**
     * Finds an entry of the container by its identifier and returns it.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * @throws ContainerExceptionInterface Error while retrieving the entry.
     *
     * @return mixed Entry.
     */
    public function get($id)
    {
        if (!$this->isSingletonClassBind($id)) {
            return $this->container->make($id);
        }

        return $this->container->get($id);
    }

    private function isSingletonClassBind($id)
    {
        if ($this->isClassString($id) && !in_array($id, $this->allBinds)) {
            // This is a class and hasn't been previously bound to the container, we should assume
            // it shouldn't be a singleton as this is our default stance
            return false;
        }

        return !in_array($id, $this->nonSingletonClassBinds);
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     *
     * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
     * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
     *
     * @param string $id Identifier of the entry to look for.
     *
     * @return bool
     */
    public function has($id)
    {
        return $this->container->has($id);
    }

    public function register($provider)
    {
        if ($foundProvider = $this->getProvider($provider)) {
            return $foundProvider;
        }

        if (is_string($provider)) {
            $provider = new $provider($this);
        }

        if (method_exists($provider, 'register')) {
            $provider->register();
        }

        $this->loadedProviders[] = $provider;

        if ($this->booted) {
            $this->bootProvider($provider);
        }

        return $provider;
    }

    public function getProvider($provider)
    {
        $providerClass = is_string($provider) ? $provider : get_class($provider);

        return (new Collection($this->loadedProviders))->first(function ($provider) use ($providerClass) {
            return get_class($provider) === $providerClass;
        });
    }

    public function getLoadedProviders()
    {
        return $this->loadedProviders;
    }

    public function boot()
    {
        if ($this->booted) {
            return;
        }

        foreach ($this->loadedProviders as $provider) {
            $this->bootProvider($provider);
        }

        $this->booted = true;
    }

    private function bootProvider($provider)
    {
        if (method_exists($provider, 'boot')) {
            $this->container->call([$provider, 'boot']);
        }
    }

    public function isBooted()
    {
        return $this->booted;
    }

    public function bootstrapWith(array $bootstrappers)
    {
        foreach ($bootstrappers as $bootstrapper) {
            $this->make($bootstrapper)->bootstrap($this);
        }
    }

    /**
     * Has the current request been handled by Lumberjack or underlying WordPress
     *
     * This is specifically useful for catching errors with WordPress Controllers, e.g. typos in
     * the Controller name
     *
     * @return boolean
     */
    public function hasRequestBeenHandled() : bool
    {
        return $this->requestHandled;
    }

    /**
     * Flag that the request has been handled
     *
     * @return void
     */
    public function requestHasBeenHandled()
    {
        $this->requestHandled = true;
    }

    /**
     * Detect when the request has not been handled and throw an exception
     *
     * @return void
     */
    public function detectWhenRequestHasNotBeenHandled()
    {
        add_action('wp_footer', function () {
            $this->requestHasBeenHandled();
        });

        add_action('shutdown', function () {
            if (!$this->hasRequestBeenHandled()) {
                if ($this->has('__wp-controller-miss-template') && $this->has('__wp-controller-miss-controller')) {
                    wp_die(
                        'Loaded template <code>' .
                        $this->get('__wp-controller-miss-template') .
                        '</code> but couldn\'t find class <code>' .
                        $this->get('__wp-controller-miss-controller') .
                        '</code>'
                    );
                }
            }
        });
    }

    public function shutdown(ResponseInterface $response = null)
    {
        if ($response) {
            global $wp;
            $wp->send_headers();

            // If we're handling a WordPressController response at this point then WordPress will already have
            // sent headers as it happens earlier in the lifecycle. For this scenario we need to do a bit more
            // work to make sure that duplicate headers are not sent back.
            send($this->removeSentHeadersAndMoveIntoResponse($response));
        }

        die();
    }

    protected function removeSentHeadersAndMoveIntoResponse(ResponseInterface $response) : ResponseInterface
    {
        // 1. Format the previously sent headers into an array of [key, value]
        // 2. Remove all headers from the output that we find
        // 3. Filter out any headers that would clash with those already in the response
        $headersToAdd = collect(headers_list())->map(function ($header) {
            $parts = explode(':', $header, 2);
            header_remove($parts[0]);

            return $parts;
        })->filter(function ($header) {
            return !in_array(strtolower($header[0]), ['content-type']);
        });

        // Add the previously sent headers into the response
        // Note: You can't mutate a response so we need to use the reduce to end up with a response
        // object with all the correct headers
        $responseToSend = collect($headersToAdd)->reduce(function ($newResponse, $header) {
            return $newResponse->withAddedHeader($header[0], $header[1]);
        }, $response);

        return $responseToSend;
    }

    /**
     * Is PHP being run from a CLI
     *
     * @return boolean
     */
    public function runningInConsole()
    {
        return in_array(php_sapi_name(), ['cli', 'phpdbg']);
    }
}
