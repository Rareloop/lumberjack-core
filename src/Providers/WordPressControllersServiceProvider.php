<?php

namespace Rareloop\Lumberjack\Providers;

use Psr\Http\Message\RequestInterface;
use Rareloop\Lumberjack\Application;
use Rareloop\Router\Invoker;
use Rareloop\Router\ResponseFactory;
use Zend\Diactoros\Response;
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
        }
    }

    public function getControllerClassFromTemplate($template)
    {
        $controllerName = studly_case(basename($template, '.php')).'Controller';

        $controllerName = apply_filters('lumberjack_controller_name', $controllerName);
        $controllerNamespace = apply_filters('lumberjack_controller_namespace', 'Theme\\');

        return $controllerNamespace.$controllerName;
    }

    public function handleRequest(RequestInterface $request, $controllerName, $methodName)
    {
        if (!class_exists($controllerName)) {
            return false;
        }

        $invoker = new Invoker($this->app);
        $output = $invoker->setRequest($request)->call([$controllerName, $methodName]);
        return ResponseFactory::create($output);
    }
}
