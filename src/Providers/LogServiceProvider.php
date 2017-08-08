<?php

namespace Rareloop\Lumberjack\Providers;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Rareloop\Lumberjack\Application;

class LogServiceProvider
{
    private $app;

    public function register(Application $app)
    {
        $this->app = $app;

        $logger = new Logger('app');
        $logger->pushHandler(new StreamHandler($app->logsPath(), $this->getLogLevel()));

        $app->bind('logger', $logger);
        $app->bind(Logger::class, $logger);
    }

    private function getLogLevel()
    {
        $logLevel = Logger::DEBUG;

        if ($this->app->has('config')) {
            $logLevel = $this->app->get('config')->get('app.log_level', $logLevel);
        }

        return $logLevel;
    }
}
