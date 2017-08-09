<?php

namespace Rareloop\Lumberjack\Providers;

use Psr\Http\Message\RequestInterface;
use Rareloop\Lumberjack\Application;
use Rareloop\Router\Router;

class RouterServiceProvider
{
    protected $app;

    public function register(Application $app)
    {
        $this->app = $app;

        $router = new Router($app);

        $app->bind('router', $router);
        $app->bind(Router::class, $router);

        add_action('wp_loaded', function () {
            $request = ServerRequestFactory::fromGlobals(
                $_SERVER,
                $_GET,
                $_POST,
                $_COOKIE,
                $_FILES
            );

            $this->processRequest($request);
        });
    }

    public function processRequest(RequestInterface $request)
    {
        $response = $this->app->get('router')->match($request);

        if ($response->getStatusCode() === 404) {
            return;
        }

        $this->app->shutdown($response);
    }
}
