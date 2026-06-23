<?php

namespace Rareloop\Lumberjack\Bootstrappers;

use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Http\ServerRequest;
use Laminas\Diactoros\ServerRequestFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;

class RegisterRequestHandler
{
    public function bootstrap(Application $app)
    {
        $config = $app->get('config');

        if ($config->get('app.debug')) {
            $app->detectWhenRequestHasNotBeenHandled();
        }

        if (!$app->has('request')) {
            $request = ServerRequest::fromRequest(ServerRequestFactory::fromGlobals(
                $_SERVER,
                $_GET,
                $_POST,
                $_COOKIE,
                $_FILES
            ));

            $app->bind('request', $request);
            $app->bind(RequestInterface::class, $request);
            $app->bind(ServerRequestInterface::class, $request);
            $app->bind(ServerRequest::class, $request);
        }

        $app->bind(\WP_Query::class, function () {
            return $GLOBALS['wp_query'];
        });
    }
}
