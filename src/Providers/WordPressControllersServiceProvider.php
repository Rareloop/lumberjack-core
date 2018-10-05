<?php

namespace Rareloop\Lumberjack\Providers;

use Psr\Http\Message\RequestInterface;
use Rareloop\Router\Invoker;
use Rareloop\Router\ResponseFactory;
use Stringy\Stringy;
use Zend\Diactoros\ServerRequestFactory;

class WordPressControllersServiceProvider extends ServiceProvider
{
    public function boot()
    {
        add_filter('template_include', [$this, 'handleTemplateInclude']);
    }

    public function handleTemplateInclude($template)
    {
        include $template;

        $controller = $this->getControllerClassFromTemplate($template);

        $request = ServerRequestFactory::fromGlobals(
            $_SERVER,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES
        );

        $response = $this->handleRequest($request, $controller, 'handle');

        if ($response) {
            $this->app->shutdown($response);
        } else {
            $this->app->bind('__wp-controller-miss-template', basename($template));
            $this->app->bind('__wp-controller-miss-controller', $controller);
        }
    }

    public function getControllerClassFromTemplate($template)
    {
        $controllerName = Stringy::create(basename($template, '.php'))->upperCamelize().'Controller';

        // Classes can't start with a number so we have to special case the behaviour here
        if ($controllerName === '404Controller') {
            $controllerName = 'Error' . $controllerName;
        }

        $controllerName = apply_filters('lumberjack_controller_name', $controllerName);
        $controllerNamespace = apply_filters('lumberjack_controller_namespace', 'App\\');

        return $controllerNamespace.$controllerName;
    }

    public function handleRequest(RequestInterface $request, $controllerName, $methodName)
    {
        if (!class_exists($controllerName)) {
            if ($this->app->has('logger')) {
                $this->app->get('logger')->warning('Controller class `' . $controllerName . '` not found');
            }

            return false;
        }

        $this->app->requestHasBeenHandled();

        $invoker = new Invoker($this->app);
        $output = $invoker->setRequest($request)->call([$controllerName, $methodName]);
        return ResponseFactory::create($output, $request);
    }
}
