<?php

namespace Rareloop\Lumberjack\Providers;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Rareloop\Lumberjack\Application;

class LogServiceProvider extends ServiceProvider
{
    public function register()
    {
        $logger = new Logger('app');
        $logger->pushHandler(new StreamHandler($this->app->logsPath(), $this->getLogLevel()));

        $this->app->bind('logger', $logger);
        $this->app->bind(Logger::class, $logger);
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
