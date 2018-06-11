<?php

namespace Rareloop\Lumberjack\Session;

use Rareloop\Lumberjack\Application;
use Rareloop\Lumberjack\Config;
use Rareloop\Lumberjack\Manager;

class SessionManager extends Manager
{
    protected $config;
    protected $name;

    public function __construct(Application $app)
    {
        parent::__construct($app);

        $this->config = $this->app->get(Config::class);

        $this->name = $this->config->get('session.cookie', 'lumberjack');
    }

    public function getDefaultDriver()
    {
        return $this->config->get('session.driver', 'file');
    }

    public function createFileDriver()
    {
        $handler = new FileSessionHandler($this->getFileDriverStoragePath());

        return $this->buildSession($handler);
    }

    protected function getFileDriverStoragePath()
    {
        $path = session_save_path();

        if (empty($path)) {
            $path = sys_get_temp_dir();
        }

        return $path;
    }

    protected function buildSession($handler)
    {
        return new Store($this->name, $handler, ($_COOKIE[$this->name] ?? null));
    }
}
